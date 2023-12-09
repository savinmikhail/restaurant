<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\Services\api\user_app\OrderService;

class OrderController extends ApiController
{
    private OrderService $orderService;

    public function __construct($id, $module, OrderService $orderService, $config = [])
    {
        $this->orderService = $orderService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'index' => ['POST'],
                    'pay' => ['POST'],
                    'checkConfirmed' => ['GET'],
                    'checkPaid' => ['GET'],
                    'sbpPayTest' => ['POST'],
                    'cancel' => ['POST'],
                    'list' => ['GET'],
                    'waiter' => ['GET'],
                    'markOrderAsPaid' => ['POST'],
                    'changeOrder' => ['POST'],
                    'setOrderGuid' => ['POST'],
                ],
            ],
        ]);
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
        list($code, $data) = $this->orderService->handleOrderCreation();
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
        list($code, $data) = $this->orderService->checkConfirmed($orderId);
        $this->sendResponse($code, $data);
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
        list($code, $data) = $this->orderService->checkPaid($tinkoffPaymentId);
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
        list($code, $data) = $this->orderService->cancelOrder($orderId);
        $this->sendResponse($code, $data);
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
        list($code, $data) = $this->orderService->callWaiter();
        $this->sendResponse($code, $data);
    }

    /**
     * Mark order as paid
     *
     * @SWG\Get(path="/api/user_app/order/mark-order-as-paid",
     *     tags={"UserApp\Order"},
     *      @SWG\Parameter(
     *          name="orderId",
     *          in="formData",
     *          type="string",
     *          description="GUID заказа на стороне айки"
     *      ),
     *      @SWG\Parameter(
     *          name="paid",
     *          in="formData",
     *          type="integer",
     *          description="Оплачен или нет"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionMarkOrderAsPaid()
    {
        //сюда айко пошлет уведомление об изменении статуса заказа
        $orderGUID = \Yii::$app->request->post('orderId');
        $isPaid = (int)\Yii::$app->request->post('paid');

        list($code, $data) = $this->orderService->markOrderAsPaid($orderGUID, $isPaid);
        $this->sendResponse($code, $data);
    }

    /**
     * Change the order composition based on changes from the iiko terminal
     *
     * @SWG\Get(path="/api/user_app/order/change-order",
     *     tags={"UserApp\Order"},
     *      @SWG\Parameter(
     *          name="orderId",
     *          in="formData",
     *          type="string",
     *          description="GUID заказа на стороне айки"
     *      ),
     *      @SWG\Parameter(
     *          name="items",
     *          in="formData",
     *          type="array",
     *          items=@SWG\Items(
     *             type="integer",
     *             description="массив товаров"
     *         ),
     *          description="Массив товаров. не доделано"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionChangeOrder()
    {
        //если заказ был изменен через терминал айки, сюда прилетят изменения
        $orderGuid = \Yii::$app->request->post('orderId');
        $items = \Yii::$app->request->post('items');

        list($code, $data) = $this->orderService->handleIikoOrderChanges($orderGuid, $items);
        $this->sendResponse($code, $data);
    }

    /**
     * Mark order as paid
     *
     * @SWG\Get(path="/api/user_app/order/set-order-guid",
     *     tags={"UserApp\Order"},
     *      @SWG\Parameter(
     *          name="orderId",
     *          in="formData",
     *          type="string",
     *          description="айди заказа"
     *      ),
     *      @SWG\Parameter(
     *          name="orderGuid",
     *          in="formData",
     *          type="string",
     *          description="ГУИД заказа на стороне айки"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionsSetOrderGuid()
    {
        //сюда айко транспорт отправит гуид созданного заказа, сохраним его
        $orderId = \Yii::$app->request->post('orderId');
        $orderGUID = \Yii::$app->request->post('orderGuid');

        list($code, $data) = $this->orderService->setOrderGUID($orderId, $orderGUID);
        $this->sendResponse($code, $data);
    }
}
