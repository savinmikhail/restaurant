<?php

namespace app\controllers\api\user_app;

use app\controllers\api\user_app\OrderableController;
use app\models\tables\Order;
use app\Services\api\user_app\OrderService;

class OrderController extends OrderableController
{
    private $orderService;

    public function __construct($id, $module, OrderService $orderService, $config = [])
    {
        $this->orderService = $orderService;
        parent::__construct($id, $module, $config);
    }
    
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
        list($code, $data) = $this->orderService->createOrder();
        $this->sendResponse($code, $data);
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
        $orderIds = $request->post('orderId');
      
        list($code, $data) = $this->orderService->pay($orderIds, $paymentMethod);
        $this->sendResponse($code, $data);
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
        $orderId = $request->post('id');
        $order = Order::find()->where(['id' => $orderId])->one();
        $order->canceled = 1;
        $order->updated_at = time();
        if(!$order->save()){
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    public function actionList()
    {
        $output = $this->orderService->getOrderList();
        $this->sendResponse(200, $output);
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
