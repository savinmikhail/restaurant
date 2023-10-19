<?php

namespace app\controllers\api;

use app\common\Util;
use app\models\tables\Basket;
use app\controllers\api\OrderableController;
use app\models\tables\PaymentType;
use app\models\tables\Table;

class BasketController extends OrderableController
{

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
            ],
        ];

        return $behaviors;
    }

    /**
     * @SWG\Get(path="/api/basket",
     *     tags={"Basket"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        list($result, $total) = $this->getBasketItems();

        $output = [];

        foreach ($result['items'] as $item) {
            $sizeName = '';

            // Find the matching size name
            foreach ($item['product']['productSizePrices'] as $sizePrice) {
                if ($sizePrice['size_id'] == $item['size_id']) {
                    $sizeName = $sizePrice['size']['name'];
                    break; // Break out of the loop once the correct size name is found
                }
            }

            $restructuredItem = [
                'productId' => $item['product_id'],
                'image'     => $item['product']['image'],
                'name'      => $item['product']['name'],
                'quantity'  => $item['quantity'],
                'price'     => $item['price'],
                'size'      => $sizeName,
                'sizeId'    => $item['size_id']
            ];

            $output[] = $restructuredItem;
        }


        $data = [
            'data' => [
                'list' => $output,
                'count' => $this->getBasketItemsCount(),
                'total' => $total,
            ]

        ];
        
        $this->sendResponse(200, $data);
    }

    /**
     * @SWG\Post(path="/api/basket/paymentmethods",
     *     tags={"Basket"},
     *     description="Справочник способов оплаты",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionPaymentmethods()
    {
        $paymentTypes = PaymentType::find()->asArray()->pluck('name')->all();

        return $this->asJson(
            [
                'list' => $paymentTypes
            ]
        );
    }

    /**
     * @SWG\Post(path="/api/basket",
     *     tags={"Basket"},
     *      @SWG\Parameter(
     *      name="product_id",
     *      in="formData",
     *      type="integer"
     *      ),
     *      @SWG\Parameter(
     *      name="size_id",
     *      in="formData",
     *      type="integer"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Добавить товар в корзину",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionAdd()
    {
        $request = \Yii::$app->request->post();
        list($productId, $quantity,  $sizeId) = [
            $request['product_id'],
            1,
            $request['size_id']
        ];
        try {
            $this->getBasket()->addItem($productId, $quantity, $sizeId);
        } catch (\Exception $e) {
            return $this->asJson(['status' => 0, 'error' => $e->getMessage()]);
        }
        list($result) = $this->getBasketItems();
        Util::prepareItems($result['items']);

        return $this->returnResponse();
    }

    private function returnResponse()
    {
        list($result, $total) = $this->getBasketItems();

        $data = [
            'data' => [
                'list' => $result,
                'count' => $this->getBasketItemsCount(),
                'total' => $total,
            ]

        ];
        $this->sendResponse(200, $data);
    }

    /**
     * @SWG\Put(path="/api/basket",
     *     tags={"Basket"},
     *      @SWG\Parameter(
     *      name="productId",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="quantity",
     *      in="formData",
     *      type="string"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Изменить количество товара в корзине",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionSet()
    {
        $request = \Yii::$app->request;

        $this->getBasket()->updateItem($request->post('productId'), $request->post('quantity'));
        return $this->returnResponse();
    }

    /**
     * @SWG\Delete(path="/api/basket",
     *     tags={"Basket"},
     *      @SWG\Parameter(
     *      name="id",
     *      in="formData",
     *      type="string"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Удалить товар из корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionDelete()
    {
        $request = \Yii::$app->request;

        $this->getBasket()->deleteItem($request->post('id'));
        return $this->returnResponse();
    }

    /**
     * @SWG\Post(path="/api/basket/clear",
     *     tags={"Basket"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Очистить корзину",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionClear()
    {
        $this->getBasket()->clear();
        return $this->returnResponse();
    }

    private function getBasketItemsCount()
    {
        $obTable = Table::getTable();

        return Basket::find()
            ->joinWith('items')
            ->joinWith('items.product')
            ->where(
                [
                    'table_id' => $obTable->id,
                ]
            )
            ->cache(false)
            ->asArray()
            ->count();
    }
}
