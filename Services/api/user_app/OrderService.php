<?php

namespace app\Services\api\user_app;

use app\models\tables\Basket;
use app\models\tables\BasketItem;
use app\models\tables\Order;
use app\models\tables\Payment;
use app\models\tables\Products;
use app\models\tables\Setting;
use app\models\tables\Table;
use app\Services\QRGen;
use app\Services\api\user_app\Payment as User_appPayment;

class OrderService
{
    private User_appPayment $paymentService;

    public function __construct(User_appPayment $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function pay(array $orderIds, string $paymentMethod): array
    {
        try {
            $transaction = \Yii::$app->db->beginTransaction();
            // фронт шлет несколько заказов на оплату, объединяю в один
            $payment = $this->createPayment($orderIds, $paymentMethod);
            //проверяю условия оплаты
            $result = $this->processPaymentMethod($payment);
            //очищаю корзину
            $this->refreshBasket();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [400, ['data' => $e->getMessage()]];
        }

        return [200, $result];
    }

    public function getStatus(int $tinkoffPaymentId): array
    {
        $tinkoffResponse = $this->paymentService->getState($tinkoffPaymentId);
        if ($tinkoffResponse['Success']) {
            return [200, $tinkoffResponse['Status']];
        } else {
            return [400, $tinkoffResponse['Message']];
        }
    }

    public function sbpPayTest(int $tinkoffPaymentId): array
    {
        $tinkoffResponse = $this->paymentService->sbpPayTest($tinkoffPaymentId);
        if ($tinkoffResponse['Success']) {
            return [200, ['Paid' => true]];
        } else {
            return [400, $tinkoffResponse['Message']];
        }
    }

    //заказ сформирован, отправлен на оплату, удаляю корзину для стола и создаю новую, чтобы очистить корзину от товаров, но оставить товары для заказа
    private function refreshBasket()
    {
        $table = Table::getTable();
        $basket = Basket::find()->where(['table_id' => $table->id])->one();
        if (!$basket) {
            throw new \Exception('Basket not found');
        }
        $basket->delete();

        $basket = new Basket();
        $basket->table_id = $table->id;
        if (!$basket->save()) {
            throw new \Exception('Failed to save new basket: ' . json_encode($basket->errors));
        }
    }

    private function createPayment(array $orderIds, string $paymentMethod): Payment
    {
        $payment = new Payment();

        if (empty($orderIds)) {
            throw new \Exception('No order IDs provided');
        }
        $orders = Order::find()->where(['id' => $orderIds])->all();
        if (empty($orders)) {
            throw new \Exception('No orders found');
        }

        $orderSum = 0;
        foreach ($orders as $order) {
            $orderSum += $order->order_sum;
        }

        $payment->sum = $orderSum;
        $payment->order_ids = json_encode($orderIds);
        $payment->payment_method = $paymentMethod;
        if (!$payment->save()) {
            throw new \Exception('Failed to save payment: ' . json_encode($payment->errors));
        }
        return $payment;
    }

    /**
     * Process the payment method and return the result as an array.
     *
     * @param Payment $payment The payment object to process.
     * @throws \Exception If an error occurs during processing.
     * @return array The processed payment result.
     */
    private function processPaymentMethod(Payment $payment): array
    {
        $result = [
            'payment_id' => $payment->id
        ];

        if ($payment->payment_method === 'Cash') {
            $result['data'] = 'You need to call the waiter for paying cash';
            return $result;
        }
        try {
            list($paymentLink, $paymentId) = $this->paymentService->getTinkoffPaymentUrl($payment);
        } catch (\Exception $e) {
            throw $e;
        }
        $result['payment_link'] = $paymentLink;
        $result['payment_id'] = $paymentId;
        return $result;
    }

    /**
     * Retrieves the list of orders for the current table.
     *
     * @return array The list of orders.
     */
    public function getOrderList(): array
    {
        $obTable = Table::getTable();
        $filter = ['orders.table_id' => $obTable->id];

        $query = Order::find()
            ->where($filter)
            ->joinWith('basket')
            ->joinWith('basket.items')
            ->joinWith('basket.items.product')
            ->joinWith('basket.items.size')
            ->andWhere(['canceled' => 0])
            ->addGroupBy('orders.id')
            ->addOrderBy(['orders.id' => SORT_DESC]);

        $ordersList = $query->asArray()->all();
        $output = $this->restructurizeOrders($ordersList);
        return $output;
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
            foreach ($order['basket']['items'] as $item) {
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
     * Creates an order and returns the result as an array.
     *
     * @return array The result of creating the order. The array contains the HTTP status code and the data.
     *               If the order limit is not found, returns an HTTP status code of 400 and the message 'Order limit not found'.
     *               If an exception occurs while creating the order, returns an HTTP status code of 400 and the exception message.
     *               If the order does not need to be confirmed, returns an HTTP status code of 201 and the order data.
     *               If the order limit is reached, returns an HTTP status code of 200, the message 'order limit is reached',
     *               and the QR code for the order.
     */
    public function createOrder(): array
    {
        $tableId = Table::getTable()->id;
        $basket = Basket::find()->where(['table_id' => $tableId])->one();
        $obSetting = Setting::find()->where(['name' => 'order_limit'])->one();

        if (!$obSetting) {
            return array(400, ['data' => 'Order limit not found']);
        }
        $order = new Order();
        try {
            $order->make(['basket_id' => $basket->id, 'table_id' => $tableId, 'basket_total' => $basket->basket_total]);
        } catch (\Exception $e) {
            return array(400, ['data' => $e->getMessage()]);
        }

        $mustBeConfirmed = $order->order_sum >= $obSetting->value;
        if (!$mustBeConfirmed) {
            return array(201, ['data' => $order]);
        }

        $order->confirmed = 0;
        if (!$order->save()) {
            return array(400, ['data' => 'Failed to save order']);
        }
        return array(200, ['data' => 'order limit is reached', 'qr' => QRGen::renderQR($order->id)]);
    }

    /**
     * Handles the changes to the iiko order.
     *
     * @param string $orderGuid The GUID of the order.
     * @param array $items The array of items.
     * @throws \Exception If there is an error saving the order.
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
        } catch (\Exception $e) {
            return array(400, $e->getMessage());
        }
        BasketItem::deleteAll(['is_deleted' => 1, 'order_id' => $order->id]);
        try {
            $this->calcOrderSum($order);
        } catch (\Exception $e) {
            return array(400, $e->getMessage());
        }
        return array(200, 'OK');
    }

    /**
     * Updates an item in the shopping basket.
     *
     * @param array $item The item to be updated.
     * @param int $orderId The ID of the order.
     * @throws \Exception If there is an error saving the order item.
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
            throw new \Exception('Error saving order item');
        }
    }

    /**
     * Calculates the order sum for the given order.
     *
     * @param Order $order The order for which to calculate the sum.
     * @throws \Exception If there is an error saving the order.
     */
    private function calcOrderSum(Order $order)
    {
        $orderSum = BasketItem::find()->where(['order_id' => $order->id])->sum('quantity * price') ?? 0;
        $order->order_sum = $orderSum;
        if (!$order->save()) {
            throw new \Exception('Error saving order');
        }
    }
}
