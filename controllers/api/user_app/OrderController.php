<?php

namespace app\controllers\api\user_app;

use app\controllers\api\user_app\OrderableController;
use app\models\tables\BasketItem;
use app\models\tables\Order;
use app\models\tables\Payment;
use app\models\tables\Products;
use app\Services\api\user_app\IikoTransportService;
use app\Services\api\user_app\OrderService;

use function PHPUnit\Framework\isInstanceOf;

class OrderController extends OrderableController
{
    private OrderService $orderService;
    private IikoTransportService $iikoTransportService;

    public function __construct($id, $module, OrderService $orderService, IikoTransportService $iikoTransportService, $config = [])
    {
        $this->orderService = $orderService;
        $this->iikoTransportService = $iikoTransportService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['POST'],
                'pay' => ['POST'],
                'cancel' => ['POST'],
                'list' => ['GET'],
                'callWaiter' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Create an order.
     *
     * @SWG\Post(
     *     path="/api/user_app/order",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Create an order",
     *         @SWG\Schema(ref="#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        try {
            // Create an order
            list($code, $data) = $this->orderService->createOrder();

            // Check if order creation was successful
            if (!($data['data'] instanceof Order)) {
                $this->sendResponse($code, $data);
            }

            // Get the created order
            $order = $data['data'];

            // Send the order to iiko
            list($code, $data) = $this->iikoTransportService->sendOrder($order->id);
        } catch (\Exception $e) {
            $this->sendResponse(400, ['data' => $e->getMessage()]);
        }

        // Check if order was sent successfully
        if ($code === 200) {
            $this->sendResponse(200, ['data' => $order, 'status' => 'sent to iiko']);
        }

        // Send the response
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Post(
     *     path="/api/user_app/order/pay",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *         name="orderId",
     *         in="formData",
     *         type="array",
     *         items=@SWG\Items(
     *             type="integer",
     *             description="order id"
     *         ),
     *     ),
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Ссылка на оплату \ вызов официанта",
     *         @SWG\Schema(ref="#/definitions/Products")
     *     )
     * )
     */
    public function actionPay()
    {
        $orderIds = \Yii::$app->request->post('orderId');

        if (!$orderIds) {
            $this->sendResponse(400, ['data' => "orderId parameter is missing in request"]);
        }
        //сделано для сваггера
        if (is_string($orderIds)) {
            $orderIds = explode(',', $orderIds);
        }

        list($code, $data) = $this->orderService->pay($orderIds);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Get(path="/api/user_app/order/check-confirmed",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          type="integer",
     *          description="order id"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Подтверждение от официанта",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    public function actionCheckConfirmed()
    {
        $orderId = \Yii::$app->request->get('id');
        if (!$orderId) {
            $this->sendResponse(400, ['data' => "id parameter is missing in request"]);
        }
        $order = Order::find()
            ->where(['id' => $orderId])
            ->asArray()
            ->one();
        if (!$order) {
            $this->sendResponse(400, ['data' => "no such order finded with id $orderId"]);
        }
        if ((int) $order['confirmed'] === 1) {

            list($code, $data) = $this->iikoTransportService->sendOrder($orderId);
            if ($code === 200) {
                $this->sendResponse(200, ['status' => 'confirmed']);
            }
            $this->sendResponse(400, ['status' => 'failed to send order to iiko', 'data' => $data]);
        }
        $this->sendResponse(200, ['status' => 'waiting']);
    }

    /**
     * @SWG\Get(path="/api/user_app/order/check-paid",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *          name="tinkoff_payment_id",
     *          in="query",
     *          type="integer",
     *          description="tinkoff payment id, example: 3554385638"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Подтверждение от СБП",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    /**
     * Check if the payment has been made.
     *
     * @return void
     */
    public function actionCheckPaid()
    {
        $tinkoffPaymentId = \Yii::$app->request->get('tinkoff_payment_id');
        try {
            list($code, $data) = $this->orderService->getStatus($tinkoffPaymentId);
            if ($code === 200 && $data === 'CONFIRMED') {
                $payment = Payment::find()
                    ->where(['external_id' => $tinkoffPaymentId])
                    ->one();
                $orderIds = unserialize($payment->order_ids);
                
                Order::updateAll(['paid' => 1, 'payment_method' => $payment->payment_method], ['id' => $orderIds]);
                foreach ($orderIds as $orderId) {
                    //помечаем заказ как оплаченный для айко
                    list($code, $data) = $this->iikoTransportService->markOrderAsPaid($orderId);
                }
            }
        } catch (\Exception $e) {
            $this->sendResponse(400, ['data' => $e->getMessage()]);
        }

        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Post(path="/api/user_app/order/sbp-pay-test",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *          name="payment_id",
     *          in="formData",
     *          type="integer",
     *          description="tinkoff payment id"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "оплатить по СБП",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionSbpPayTest()
    {
        $paymentId = \Yii::$app->request->post('payment_id');
        list($code, $data) = $this->orderService->sbpPayTest($paymentId);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Post(path="/api/user_app/order/cancel",
     *     tags={"UserApp\Order"},
     *      @SWG\Parameter(
     *          name="id",
     *          in="formData",
     *          type="string",
     *          description="Ид заказа"
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
        $orderId = \Yii::$app->request->post('id');
        $order = Order::find()->where(['id' => $orderId])->one();
        $order->canceled = 1;
        $order->updated_at = time();
        if (!$order->save()) {
            $this->sendResponse(400, ['errors' => $order->errors]);
        }
        if ($order->external_id) {
            //TODO: send requst to iiko
        }
        $this->sendResponse(200, 'success');
    }

    /**
     * Retrieves a list of orders.
     *
     * @SWG\Get(path="/api/user_app/order/list",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    public function actionList()
    {
        list($code, $output) = $this->orderService->getOrderList();
        $this->sendResponse($code, $output);
    }

    /**
     * Call a waiter
     *
     * @SWG\Get(path="/api/user_app/order/waiter",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionWaiter()
    {
        list($code, $data) = $this->iikoTransportService->callWaiter();
        $this->sendResponse($code, $data);
    }

    //сюда айко пошлет уведомление об изменении статуса заказа
    public function actionMarkOrderAsPaid()
    {
        $postData = \Yii::$app->request->post();

        $order = Order::find()
            ->where(['external_id' => $postData['orderId']])
            ->one();
        $order->paid = (int) $postData['paid'];
        if (!$order->save()) {
            throw new \Exception('Error saving order');
        }
        $this->sendResponse(200, 'success');
    }

    //если заказ был изменен через терминал айки, сюда прилетят изменения
    public function actionChangeOrder()
    {
        $postData = \Yii::$app->request->post();
        $orderGuid = $postData['orderId'];
        $items = $postData['items'];
        list($code, $data) = $this->orderService->handleIikoOrderChanges($orderGuid, $items);
        $this->sendResponse($code, $data);
    }

    //сюда айко транспорт отправит гуид созданного заказа, сохраним его
    public function actionsSetOrderGuid()
    {
        $postData = \Yii::$app->request->post();
        $order = Order::find()->where(['id' => $postData['orderId']])->one();
        $order->external_id = $postData['orderGuid'];
        if (!$order->save()) {
            throw new \Exception('Error saving order');
        }
        $this->sendResponse(200, 'success');
    }
}
