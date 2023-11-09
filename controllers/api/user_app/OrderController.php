<?php

namespace app\controllers\api\user_app;

use app\controllers\api\user_app\OrderableController;
use app\models\tables\Basket;
use app\models\tables\Order;
use app\models\tables\Setting;
use app\models\tables\Table;
use app\Services\QRGen;

class OrderController extends OrderableController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index'  => ['POST'],
                'pay' => ['POST'],
                'cancel' => ['POST'],
                'list' => ['GET'],
                'callWaiter' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @SWG\Post(path="/api/user_app/order",
     *     tags={"UserApp\Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Результат добавления заказа",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        $tableId = Table::getTable()->id;
        $basket = Basket::find()->where(['table_id' => $tableId])->one();
        $obSetting = Setting::find()->where(['name' => 'order_limit'])->one();

        $order = new Order();
        try {
            $result = $order->make(['basket_id' => $basket->id, 'table_id' => $tableId, 'basket_total' => $basket->basket_total]);
        } catch (\Exception $e) {
            $this->sendResponse(400, ['data' => $e->getMessage()]);
        }

        if ($obSetting && $order->order_sum >= $obSetting->value) {
            $this->sendResponse(200, ['data' => 'order limit is reached', 'qr' => QRGen::renderQR($tableId)]);
        }

        if ($result) {
            $this->sendResponse(201, ['data' => $order]);
        }
    }

    /**
     * @SWG\Post(path="/api/user_app/order/pay",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *          name="orderId",
     *          in="formData",
     *          type="integer",
     *          description="order id"
     *     ),
     *     @SWG\Parameter(
     *          name="paymentMethod",
     *          in="formData",
     *          type="string",
     *          description="метод оплаты"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Ссылка на оплату \ вызов официанта",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionPay()
    {
        $request = \Yii::$app->request;
        $paymentMethod = $request->post('paymentMethod');
        $orderId = $request->post('orderId');
        is_array($request->post('orderId')) ? $orderIds = $request->post('orderId') : $orderId = $request->post('orderId');
        if ($orderIds) {
            $orderId = $this->uniteOrders($orderIds);
        }
        if (!$orderId) {
            $this->sendResponse(400, ['data' => 'Order not found']);
        }
        $order = Order::find()->where(['id' => $orderId])->one();

        if ($order) {
            $order->payment_method = $paymentMethod;
            if (!$order->save()) {
                $this->sendResponse(400, ['data' => $order->errors]);
            }
            $this->finalAction($order);
        }
        $this->sendResponse(400, ['data' => 'Order not found']);
    }

    private function uniteOrders(array $orderIds): ?int
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $orders = Order::find()->where(['id' => $orderIds])->all();
            if (empty($orders)) {
                throw new \Exception('No orders found.');
            }

            $orderSum = 0;
            foreach ($orders as $order) {
                $orderSum += $order->order_sum;
            }

            $newOrder = new Order;
            $newOrder->order_sum = $orderSum;
            $newOrder->table_id = $orders[0]->table_id;
            $newOrder->basket_id = $orders[0]->basket_id;

            if (!$newOrder->save()) {
                throw new \Exception('Failed to save new order: ' . json_encode($newOrder->errors));
            }

           Order::deleteAll(['id' => $orderIds]);

            $transaction->commit();
            return $newOrder->id;
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e->getMessage(), __METHOD__);
            return null;
        }
    }

    private function finalAction(Order $order)
    {
        $result = [
            'order_id' => $order->id
        ];

        if ($order->payment_method === 'Cash') {
            return $this->asJson(['success' => true, 'data' => 'You need to call the waiter for paying cash']);
        } else {
            $result['qr'] = QRGen::renderQR($order->id); //TODO: implement sbp payment
            // list($status, $result['paymentUrl'], $result['bankUrl']) = Payment::createSberPaymentUrl($order->id, $order->order_sum);
        }

        $this->sendResponse(200, $result);
    }

    /**
     * @SWG\Get(path="/api/user_app/order/check-confirmed",
     *     tags={"UserApp\Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Подтверждение от официанта",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    public function actionCheckConfirmed()
    {
        sleep(3); //TODO: заменить на проверку статуса
        $this->sendResponse(200, ['status' => 'success']);
    }

    /**
     * @SWG\Get(path="/api/user_app/order/check-paid",
     *     tags={"UserApp\Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Подтверждение от СБП",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    public function actionCheckPaid()
    {
        sleep(3); //TODO: заменить на проверку статуса
        $this->sendResponse(200, ['status' => 'success']);
    }

    /**
     * @SWG\Post(path="/api/user_app/order/cancel",
     *     tags={"UserApp\Order"},
     *      @SWG\Parameter(
     *      name="id",
     *      in="formData",
     *      type="string",
     *      description="Ид заказа"
     *      ),
     *     description="Отмена заказа",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    public function actionCancel()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $order = Order::find()->where(['id' => $request->post('id')])->one();
        $order->canceled = 1;
        $order->updated_at = time();
        $order->save();
        if ($order->external_id) {
            //TODO: send requst to iiko 
        }

        return $this->asJson(['result' => true, 'errors' => $order->errors]);
    }

    /**
     * Retrieves a list of orders.
     *
     * @SWG\Get(path="/api/user_app/order/list",
     *     tags={"UserApp\Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    
    public function actionList()
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

        $output = [];
        $totalSum = 0;
        $totalPaid = 0;
        $unpaidCount = 0;

        foreach ($ordersList as $order) {
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

        // Output the result as JSON
        return $this->asJson($output);
    }

    /**
     * Call a waiter
     *
     * @SWG\Get(path="/api/user_app/order/waiter",
     *     tags={"UserApp\Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionWaiter()
    {
        sleep(1);
        $this->sendResponse(200, 'success');
    }
}
