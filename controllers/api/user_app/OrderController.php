<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\Services\api\user_app\OrderService;

class OrderController extends ApiController
{
    public function __construct($id, $module, private OrderService $orderService, $config = [])
    {
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
                    'check-confirmed' => ['GET'],
                    'check-paid' => ['GET'],
                    'sbp-pay-test' => ['POST'],
                    'cancel' => ['POST'],
                    'list' => ['GET'],
                    'waiter' => ['GET'],
                    'mark-order-as-paid' => ['POST'],
                    'change-order' => ['POST'],
                    'set-order-guid' => ['POST'],
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
     * @SWG\Post(path="/api/user_app/order/cancel",
     *     tags={"UserApp\Order"},
     *      @SWG\Parameter(
     *          name="orderGuid",
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
        //сюда айко транспорт сообщит, что заказ отменен
        $orderGuid = \Yii::$app->request->post('orderGuid');
        list($code, $data) = $this->orderService->cancelOrder($orderGuid);
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
     *         description = "Содержимое заказа",
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
     * @SWG\Post(path="/api/user_app/order/set-order-guid",
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
    public function actionSetOrderGuid()
    {
        //сюда айко транспорт отправит гуид созданного заказа, сохраним его
        $orderId = \Yii::$app->request->post('orderId');
        $orderGUID = \Yii::$app->request->post('orderGuid');

        list($code, $data) = $this->orderService->setOrderGUID($orderId, $orderGUID);
        $this->sendResponse($code, $data);
    }
}
