<?php

namespace app\Services\api\user_app;

use app\models\tables\Basket;
use app\models\tables\BasketItem;
use app\models\tables\Order;
use app\models\tables\Payment;
use app\models\tables\Products;
use app\models\tables\Setting;
use app\models\tables\Table;
use app\Services\api\user_app\QRGen;
use app\Services\api\user_app\Payment as User_appPayment;
use Exception;
use app\Services\api\BaseService;

class OrderService extends BaseService
{
    private User_appPayment $paymentService;
    private IikoTransportService $iikoTransportService;

    public function __construct(User_appPayment $paymentService, IikoTransportService $iikoTransportService)
    {
        $this->paymentService = $paymentService;
        $this->iikoTransportService = $iikoTransportService;
    }

    /**
     * Executes a payment for the given order IDs.
     *
     * @param array $orderIds The array of order IDs to be paid.
     * @throws Exception If an error occurs during the payment process.
     * @return array The response array with the status code and the payment result.
     */
    public function pay(array $orderIds): array
    {
        try {
            $transaction = \Yii::$app->db->beginTransaction();
            // фронт шлет несколько заказов на оплату, объединяю в один
            $payment = $this->createPayment($orderIds);
            //проверяю условия оплаты
            $result = $this->processPaymentMethod($payment);
            //очищаю корзину
            $this->refreshBasket();
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            return [self::HTTP_BAD_REQUEST, ['data' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]];
        }

        return [self::HTTP_OK, $result];
    }


    /**
     * Retrieves the status of a Tinkoff payment.
     *
     * @param int $tinkoffPaymentId The ID of the Tinkoff payment.
     * @throws Exception If there is an error retrieving the payment status.
     * @return string The status of the Tinkoff payment.
     */
    private function getStatus(int $tinkoffPaymentId): string
    {
        try {
            $tinkoffResponse = $this->paymentService->getState($tinkoffPaymentId);
        } catch (Exception $e) {
            throw $e;
        }
        if (!$tinkoffResponse['Success']) {
            throw new Exception($tinkoffResponse['Message']);
        }
        return $tinkoffResponse['Status'];
    }

    /**
     * Checks if an order is confirmed by waiter.
     *
     * @param int $orderId The ID of the order to be checked.
     * @return array Returns an array with the HTTP status code and data.
     */
    public function checkConfirmed(int $orderId): array
    {
        if (!$orderId) {
            return array(self::HTTP_BAD_REQUEST, ["data" => "id parameter is missing in request"]);
        }
        $order = Order::find()
            ->where(['id' => $orderId])
            ->asArray()
            ->one();
        if (!$order) {
            return array(self::HTTP_BAD_REQUEST, ["data" => "no such order finded with id $orderId"]);
        }
        if ((int) $order['confirmed'] !== 1) {
            return array(self::HTTP_OK, ['status' => 'waiting']);
        }

        list($code, $data) = $this->iikoTransportService->sendOrder($orderId);
        if ($code !== self::HTTP_OK) {
            return array(self::HTTP_BAD_REQUEST, ['status' => 'failed to send order to iiko', 'data' => $data]);
        }
        return array(self::HTTP_OK, ['status' => 'confirmed']);
    }

    /**
     * Checks if a Tinkoff payment is paid.
     *
     * @param int $tinkoffPaymentId The ID of the Tinkoff payment.
     * @throws Exception If an error occurs while checking the payment status.
     * @return array An array with the HTTP code and the payment status data.
     */
    public function checkPaid(int $tinkoffPaymentId): array
    {
        try {
            $status = $this->getStatus($tinkoffPaymentId);

            if ($status !== 'CONFIRMED') {
                return array(self::HTTP_OK, ['data' => $status]);
            }
            $payment = $this->markPaymentAsPaid($tinkoffPaymentId);
            $this->markOrdersAsPaid($payment);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, ['data' => $e->getMessage()]);
        }
        return array(self::HTTP_OK, ['data' => 'OK']);
    }

    /**
     * Marks a payment as paid.
     *
     * @param int $tinkoffPaymentId The Tinkoff payment ID.
     * @throws Exception If the payment is not found or fails to save.
     * @return Payment The updated payment object.
     */
    private function markPaymentAsPaid(int $tinkoffPaymentId): Payment
    {
        $payment = Payment::find()
            ->where(['external_id' => $tinkoffPaymentId])
            ->one();
        if (!$payment) {
            throw new Exception('Payment not found');
        }
        $payment->paid = 1;
        if (!$payment->save()) {
            throw new Exception('Failed to save payment: ' . json_encode($payment->errors));
        }
        return $payment;
    }

    /**
     * Marks the orders as paid for a given payment.
     *
     * @param Payment $payment The payment object.
     * @throws Exception If there is an error in unserializing the order IDs or marking the order as paid in the iiko transport service.
     * @return void
     */
    private function markOrdersAsPaid(Payment $payment): void
    {

        $orderIds = unserialize($payment->order_ids);
        if ($orderIds === false) {
            throw new Exception('Failed to unserialize order IDs');
        }

        Order::updateAll(['paid' => 1, 'payment_method' => $payment->payment_method], ['id' => $orderIds]);
        foreach ($orderIds as $orderId) {
            //помечаем заказ как оплаченный для айко
            list($code, $data) = $this->iikoTransportService->markOrderAsPaid($orderId);
            if ($code !== self::HTTP_OK) {
                throw new Exception($data);
            }
        }
    }

    /**
     * Executes a test payment using the Tinkoff payment service.
     *
     * @param int $tinkoffPaymentId The ID of the Tinkoff payment.
     * @return array Returns an array with the HTTP status code and the payment status.
     */
    public function sbpPayTest(int $tinkoffPaymentId): array
    {
        $tinkoffResponse = $this->paymentService->sbpPayTest($tinkoffPaymentId);
        if ($tinkoffResponse['Success']) {
            return [self::HTTP_OK, ['Paid' => true]];
        } else {
            return [self::HTTP_BAD_REQUEST, $tinkoffResponse['Message']];
        }
    }

    /**
     * Refreshes the basket for the current table.
     *
     * @throws Exception if the table is not found or the basket is not found
     * @return void
     */
    private function refreshBasket()
    {
        //заказ сформирован, отправлен на оплату, удаляю корзину для стола и создаю новую, чтобы очистить корзину от товаров, но оставить товары для заказа
        $table = Table::getTable();
        if (!$table) {
            throw new Exception('Table not found');
        }
        $basket = Basket::find()
            ->where(['table_id' => $table->id])
            ->one();
        if (!$basket) {
            throw new Exception('Basket not found');
        }
        $basket->delete();

        $basket = new Basket();
        $basket->table_id = $table->id;
        if (!$basket->save()) {
            throw new Exception('Failed to save new basket: ' . json_encode($basket->errors));
        }
    }

    /**
     * Creates a payment using the provided order IDs.
     *
     * @param array $orderIds An array of order IDs.
     * @throws Exception If no order IDs are provided or if no orders are found.
     * @return Payment The created payment object.
     */
    private function createPayment(array $orderIds): Payment
    {
        $payment = new Payment();

        if (empty($orderIds)) {
            throw new Exception('No order IDs provided');
        }
        $orders = Order::find()->where(['id' => $orderIds])->all();
        if (empty($orders)) {
            throw new Exception('No orders found');
        }

        $orderSum = 0;
        foreach ($orders as $order) {
            $orderSum += $order->order_sum;
        }

        $payment->sum = $orderSum;
        $payment->order_ids = serialize($orderIds);
        $payment->payment_method = 'sbp'; //так как пользователь тыкнул на оплатить куар кодом, пишем этот тип платежа
        if (!$payment->save()) {
            throw new Exception('Failed to save payment: ' . json_encode($payment->errors));
        }
        return $payment;
    }

    /**
     * Process the payment method and return the result as an array.
     *
     * @param Payment $payment The payment object to process.
     * @throws Exception If an error occurs during processing.
     * @return array The processed payment result.
     */
    private function processPaymentMethod(Payment $payment): array
    {
        try {
            list($paymentQr, $tinkoffPaymentId) = $this->paymentService->getTinkoffPaymentQr($payment);
        } catch (Exception $e) {
            throw $e;
        }
        $payment->external_id = $tinkoffPaymentId;
        if (!$payment->save()) {
            throw new Exception('Failed to save payment: ' . json_encode($payment->errors));
        }
        $result = [
            'payment_id' => $payment->id,
            'tinkoff_payment_id' => $tinkoffPaymentId,
            'qr' => $paymentQr,
        ];
        return $result;
    }

    /**
     * Retrieves the list of orders for a specific table.
     *
     * @return array The list of orders for the table.
     */
    public function getOrderList(): array
    {
        $obTable = Table::getTable();
        if (!$obTable) {
            return [self::HTTP_BAD_REQUEST, 'Table not found'];
        }
        $filter = ['orders.table_id' => $obTable->id];

        $query = Order::find()
            ->where($filter)
            ->joinWith('basket')
            ->joinWith('items.product')
            ->joinWith('items.size')
            ->andWhere(['canceled' => 0])
            ->addGroupBy('orders.id')
            ->addOrderBy(['orders.id' => SORT_DESC]);

        $ordersList = $query->asArray()->all();
        $output = $this->restructurizeOrders($ordersList);
        return [self::HTTP_OK, $output];
    }

    /**
     * Restructurizes an array of orders.
     *
     * @param array $orders The array of orders to be restructurized.
     * @return array The restructurized array of orders.
     */
    private function restructurizeOrders(array $orders): array
    {
        $output = [];
        $totalSum = 0;
        $totalPaid = 0;
        $unpaidCount = 0;

        foreach ($orders as $order) {
            $items = [];
            if (is_null($order['basket'])) {
                continue;
            }
            $orderTotal = $order['basket']['basket_total'];

            // Increment the total sum
            $totalSum += $orderTotal;

            // Check if the order is paid
            if ($order['paid']) {
                $totalPaid += $orderTotal;
            } else {
                $unpaidCount++;
            }

            // Iterate through the items in the order's basket
            foreach ($order['items'] as $item) {
                $items[] = [
                    'productId' => $item['product_id'],
                    'image' => $item['product']['image'],
                    'name' => $item['product']['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'size' => $item['size']['name'],
                    'sizeId' => $item['size_id']
                ];
            }

            // Append a new order with the desired structure to the output array
            $output['orders'][] = [
                'id' => $order['id'],
                'price' => $orderTotal,
                'isPaid' => $order['paid'] ? true : false,
                'list' => $items
            ];
        }

        // Add the computed totals to the output
        $output['totalSum'] = $totalSum;
        $output['totalPaid'] = $totalPaid;
        $output['totalUnpaid'] = $totalSum - $totalPaid;
        $output['unpaidCount'] = $unpaidCount;

        return $output;
    }

    /**
     * Handles the creation of an order.
     *
     * The function creates an order by calling the `createOrder` method. If the `qr` parameter is present in the response data,
     * it returns an array with the HTTP status code self::HTTP_OK and the data. Otherwise, it retrieves the `orderId` from the response
     * data and sends the order to iiko using the `sendOrder` method of the `iikoTransportService`. If an exception is thrown
     * during the process, it returns an array with the HTTP status code self::HTTP_BAD_REQUEST and the error message. Finally, it checks if the
     * order was sent successfully and returns an array with the HTTP status code self::HTTP_OK, the `orderId`, and the status as
     * "sent to iiko".
     *
     * @return array An array with the HTTP status code and the response data.
     * @throws Exception If an error occurs during the process.
     */
    public function handleOrderCreation()
    {
        try {
            // Create an order
            $data = $this->createOrder();

            // если куар не пришел, то ждем подтверждения официанта, прежде чем отправлять заказ в айку
            if (isset($data['qr'])) {
                return array(self::HTTP_OK, $data);
            }

            // Get the created order
            $orderId = $data['orderId'];

            // Send the order to iiko
            list($code, $data) = $this->iikoTransportService->sendOrder($orderId);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, ['data' => $e->getMessage()]);
        }

        // Check if order was sent successfully
        if ($code !== self::HTTP_OK) {
            return array($code, ['data' => $data]);
        }
        return array(self::HTTP_OK, ['orderId' => $orderId, 'status' => 'sent to iiko']);
    }


    /**
     * Creates an order.
     *
     * @throws Exception if the table is not found, the basket is not found, or the order limit is not found
     * @throws Exception if there is an error while saving the order
     * @return array an array containing the order ID and additional data
     */
    private function createOrder(): array
    {
        $table = Table::getTable();
        if (!$table) {
            throw new Exception('Table not found');
        }
        $tableId = $table->id;
        $basket = Basket::find()
            ->where(['table_id' => $tableId])
            ->one();
        if (!$basket) {
            throw new Exception('Basket not found');
        }
        $obSetting = Setting::find()
            ->where(['name' => 'order_limit'])
            ->one();

        if (!$obSetting) {
            throw new Exception('Order limit not found');
        }
        $order = new Order();
        try {
            $order->make(['basket_id' => $basket->id, 'table_id' => $tableId, 'basket_total' => $basket->basket_total]);
        } catch (Exception $e) {
            throw $e;
        }

        $mustBeConfirmed = $order->order_sum >= $obSetting->value;
        if (!$mustBeConfirmed) {
            return ['orderId' => $order->id];
        }

        $order->confirmed = 0;
        if (!$order->save()) {
            throw new Exception('Failed to save order' . print_r($order->errors, true));
        }
        return [
            'data' => 'order limit is reached',
            'qr' => QRGen::renderQR($order->id),
            'orderId' => $order->id
        ];
    }

    /**
     * Handles the changes to the iiko order.
     *
     * @param string $orderGuid The GUID of the order.
     * @param array $items The array of items.
     * @throws Exception If there is an error saving the order.
     * @return array The result of the function call.
     */
    public function handleIikoOrderChanges(string $orderGuid, array $items): array
    {
        $order = Order::find()
            ->where(['external_id' => $orderGuid])
            ->one();

        BasketItem::updateAll(['is_deleted' => 1], ['order_id' => $order->id]);
        try {
            foreach ($items as $item) {
                $this->updateItem($item, $order->id);
            }
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }
        BasketItem::deleteAll(['is_deleted' => 1, 'order_id' => $order->id]);
        try {
            $this->calcOrderSum($order);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }
        return array(self::HTTP_OK, 'OK');
    }

    /**
     * Updates an item in the shopping basket.
     *
     * @param array $item The item to be updated.
     * @param int $orderId The ID of the order.
     * @throws Exception If there is an error saving the order item.
     * @return void
     */
    private function updateItem(array $item, int $orderId)
    {
        $product = Products::find()
            ->where(['code' => $item['code']])
            ->asArray()
            ->one();
        $orderItem = BasketItem::find()
            ->where(['order_id' => $orderId])
            ->andWhere(['product_id' => $product['id']])
            ->one();
        if (!$orderItem) {
            $orderItem = new BasketItem();
            $orderItem->order_id = $orderId;
            $orderItem->product_id = $product['id'];
        }
        $orderItem->quantity = $item['quantity'];
        $orderItem->is_deleted = 0;
        if ($item['sizeId']) {
            $orderItem->size_id = $item['sizeId'];
        }
        if (!$orderItem->save()) {
            throw new Exception('Error saving order item');
        }
    }

    /**
     * Calculates the order sum for the given order.
     *
     * @param Order $order The order for which to calculate the sum.
     * @throws Exception If there is an error saving the order.
     */
    private function calcOrderSum(Order $order)
    {
        $orderSum = BasketItem::find()->where(['order_id' => $order->id])->sum('quantity * price') ?? 0;
        $order->order_sum = $orderSum;
        if (!$order->save()) {
            throw new Exception('Error saving order');
        }
    }

    public function setOrderGuid(int $orderId, string $orderGUID): array
    {
        $order = Order::find()->where(['id' => $orderId])->one();
        $order->external_id = $orderGUID;
        if (!$order->save()) {
            return array(self::HTTP_BAD_REQUEST, 'Error saving order');
        }
        return array(self::HTTP_OK, 'OK');
    }

    public function markOrderAsPaid(string $orderGUID, int $isPaid): array
    {
        $order = Order::find()
            ->where(['external_id' => $orderGUID])
            ->one();

        if (!$order) {
            return array(self::HTTP_BAD_REQUEST, 'Order not found');
        }
        $order->paid = $isPaid;
        if (!$order->save()) {
            return array(self::HTTP_BAD_REQUEST, 'Error saving order');
        }
        return array(self::HTTP_OK, 'OK');
    }

    public function cancelOrder(int $orderId): array
    {
        $order = Order::find()->where(['id' => $orderId])->one();
        $order->canceled = 1;
        $order->updated_at = time();
        if (!$order->save()) {
            return array(self::HTTP_BAD_REQUEST, ['data' => "Failed to save order: " . print_r($order->errors, true)]);
        }
        if (!$order->external_id) {
            return array(self::HTTP_BAD_REQUEST, ['data' => 'Order does not exists in iiko ']);
        }
        return array(self::HTTP_OK, ['data' => 'OK']);
        //TODO: send requst to iiko
    }

    public function callWaiter(): array
    {
        list($code, $data) = $this->iikoTransportService->callWaiter();
        return array($code, $data);
    }
}
