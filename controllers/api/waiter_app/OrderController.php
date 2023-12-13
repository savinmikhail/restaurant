<?php

namespace app\controllers\api\waiter_app;

use app\controllers\api\ApiController;
use app\Services\api\waiter_app\OrderService;

class OrderController extends ApiController
{
    public function __construct($id, $module, private OrderService $orderService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'list'  => ['GET'],
                'index' => ['GET'],
                'edit' => ['PUT'],
                'delete-item' => ['DELETE'],
                'add-item' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Retrieves a list of orders.
     *
     * @SWG\Get(path="/api/waiter_app/orders",
     *     tags={"WaiterApp\Order"},
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         type="integer"
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
        $page = \Yii::$app->request->get('page', 1);
        $perPage = \Yii::$app->request->get('perPage', 10);

        list($code, $output) = $this->orderService->getListData($page, $perPage);
        $this->sendResponse($code, $output);
    }

    /**
     * @SWG\Get(path="/api/waiter_app/order",
     *     tags={"WaiterApp\Order"},
     *     @SWG\Parameter(
     *          name="orderId",
     *          in="query",
     *          type="integer",
     *          description="order id"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое заказа",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $orderId = $request->get('orderId');

        list($code, $data) = $this->orderService->getIndexData($orderId);

        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Put(path="/api/waiter_app/order/edit",
     *     tags={"WaiterApp\Order"},
     *      @SWG\Parameter(
     *          name="item_id",
     *          in="formData",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="quantity",
     *          in="formData",
     *          type="integer"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Изменить количество товара в корзине",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $itemId = $request->post('item_id');
        $quantity = $request->post('quantity');

        list($code, $data) = $this->orderService->updateQuantity($itemId, $quantity);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Delete(path="/api/waiter_app/order/delete-item",
     *     tags={"WaiterApp\Order"},
     *      @SWG\Parameter(
     *          name="item_id",
     *          in="query",
     *          type="integer"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Удалить товар из заказа",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionDeleteItem()
    {
        $itemId = (int) \Yii::$app->request->get('item_id');

        list($code, $data) = $this->orderService->deleteItem($itemId);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Post(path="/api/waiter_app/order/add-item",
     *     tags={"WaiterApp\Order"},
     *      @SWG\Parameter(
     *          name="product_id",
     *          in="formData",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="size_id",
     *          in="formData",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="order_id",
     *          in="formData",
     *          type="integer"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Добавить позицию в заказ",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionAddItem()
    {
        $request = \Yii::$app->request->post();
        $productId = $request['product_id'] ?? null;
        $sizeId = $request['size_id'] ?? null;
        $orderId = $request['order_id'] ?? null;
        $quantity = 1; // default quantity is 1

        list($code, $data) = $this->orderService->addNewItem($productId, $sizeId, $orderId, $quantity);
        $this->sendResponse($code, $data);
    }
}
