<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\Services\api\user_app\BasketService;

class BasketController extends ApiController
{
    public function __construct($id, $module, private BasketService $basketService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index'  => ['GET'],
                'add' => ['POST'],
                'set' => ['PUT'],
                'delete' => ['DELETE'],
                'clear' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @SWG\Get(path="/api/user_app/basket",
     *     tags={"UserApp\Basket"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     * )
     */
    public function actionIndex()
    {
        list($code, $data) = $this->basketService->getDataForResponse();
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Post(path="/api/user_app/basket",
     *     tags={"UserApp\Basket"},
     *      @SWG\Parameter(
     *          name="productId",
     *          in="formData",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="sizeId",
     *          in="formData",
     *          type="integer"
     *      ),
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Добавить товар в корзину",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionAdd()
    {
        $productId = \Yii::$app->request->post('productId');
        $sizeId = \Yii::$app->request->post('sizeId');
        $quantity = 1;

        list($code, $data) = $this->basketService->addItem($productId, $quantity, $sizeId);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Put(path="/api/user_app/basket",
     *     tags={"UserApp\Basket"},
     *      @SWG\Parameter(
     *          name="productId",
     *          in="formData",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="quantity",
     *          in="formData",
     *          type="integer"
     *      ),
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Изменить количество товара в корзине",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionSet()
    {
        $productId = \Yii::$app->request->post('productId');
        $quantity = \Yii::$app->request->post('quantity');

        list($code, $data) = $this->basketService->setQuantity($productId, $quantity);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Delete(path="/api/user_app/basket",
     *     tags={"UserApp\Basket"},
     *      @SWG\Parameter(
     *      name="productId",
     *      in="formData",
     *      type="integer"
     *      ),
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Удалить товар из корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionDelete()
    {
        $productId = (int) \Yii::$app->request->get('productId');
        list($code, $data) = $this->basketService->deleteItem($productId);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Post(path="/api/user_app/basket/clear",
     *     tags={"UserApp\Basket"},
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Очистить корзину",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionClear()
    {
        list($code, $data) = $this->basketService->clearBasket();
        $this->sendResponse($code, $data);
    }
}
