<?php

namespace app\Services\api\user_app;

use app\models\tables\Basket;
use app\models\tables\Order;
use app\models\tables\Payment;
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
            // фронт шлет несколько заказов на оплату, объединяю в один
            $payment = $this->createPayment($orderIds, $paymentMethod);
            //проверяю условия оплаты
            $result = $this->processPaymentMethod($payment);
            //очищаю корзину
            $this->refreshBasket();
        } catch (\Exception $e) {
            return [400, ['data' => $e->getMessage()]];
        }

        return [200, $result];
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

        $basket = new Basket;
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

    private function processPaymentMethod(Payment $payment): array
    {
        $result = [
            'payment_id' => $payment->id
        ];

        if ($payment->payment_method === 'Cash') {
            $result = ['data' => 'You need to call the waiter for paying cash'];
        } else {
            try {
                $paymentLink = $this->paymentService->getTinkoffPaymentUrl($payment); //TODO: докрутить оплату
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
            $result['payment_link'] = $paymentLink;
        }
        return $result;
    }

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
        $output  = $this->restructurizeOrders($ordersList);
        return $output;
    }

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
            $result = $order->make(['basket_id' => $basket->id, 'table_id' => $tableId, 'basket_total' => $basket->basket_total]);
        } catch (\Exception $e) {
            return array(400, ['data' => $e->getMessage()]);
        }

        if (!$result) {
            return array(400, ['data' => 'Failed to create order']);
        }

        if ($order->order_sum >= $obSetting->value) {
            $order->confirmed = 0;
            if (!$order->save()) {
                return array(400, ['data' => 'Failed to save order']);
            }
            return array(200, ['data' => 'order limit is reached', 'qr' => QRGen::renderQR($tableId)]);
        }



        return array(201, ['data' => $order]);
    }
}
