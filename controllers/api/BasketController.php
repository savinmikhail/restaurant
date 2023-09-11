<?php

namespace app\controllers\api;

use app\models\tables\Basket;
use app\controllers\api\OrderableController;

class BasketController extends OrderableController
{


    /**
     * @SWG\Post(path="/api/basket",
     *     tags={"Basket"},
     *      @SWG\Parameter(
     *      name="address",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Ид адреса"
     *      ),
     *      @SWG\Parameter(
     *      name="is_express",
     *      in="formData",
     *      type="string",
     *      description="флаг влючения экспресс-доставки 0 / 1"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $this->getBasket()->setBasketExpressStatus(intval($request->post('is_express')));
        list($result, $total) = $this->getBasketItems();

        return $this->asJson([
            'list' => $result,
            'count' => $this->getBasketItemsCount(),
            'total' => $total,
            'discount' => 0,
            'table_number' => \Yii::$app->session->get('table_number'),
        ]);
    }

    /**
     * @SWG\Post(path="/api/basket/paymentmethods",
     *     tags={"Basket"},
     *     description="справочник способов оплаты",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionPaymentmethods()
    {
        // верстка расчитана только на 2 вида. надо править если будет 3й
        return $this->asJson(['list' => ['cash' => 'Оплата при получении', 'online' => 'Оплатить онлайн']]);
    }

    /**
     * @SWG\Post(path="/api/basket/add",
     *     tags={"Basket"},
     *      @SWG\Parameter(
     *      name="address",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Ид адреса"
     *      ),
     *      @SWG\Parameter(
     *      name="product_id",
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
     *         description = "Положить товар в корзину",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionAdd()
    {
        $request = \Yii::$app->request;

        try {
            $this->getBasket()->addItem($request->post('product_id'), $request->post('quantity'));
        } catch (\Exception $e) {
            return $this->asJson(['status' => 0, 'error' => $e->getMessage(), 'code' => $e->getCode()]);
        }
        list($result, $total) = $this->getBasketItems();

        return $this->asJson([
            'status' => 1,   
            'error' => 0, 
            'message' => '',
            'list' => $result,
            'count' => $this->getBasketItemsCount(),
            'total' => $total,
            'table_number' => \Yii::$app->session->get('table_number'),
        ]);
    }

    /**
     * @SWG\Post(path="/api/basket/set",
     *     tags={"Basket"},
     *      @SWG\Parameter(
     *      name="address",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Ид адреса"
     *      ),
     *      @SWG\Parameter(
     *      name="id",
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

        $this->getBasket()->updateItem($request->post('id'), $request->post('quantity'));
        list($result, $total) = $this->getBasketItems();

        return $this->asJson([
            'status' => 1,   
            'error' => 0, 
            'message' => '',
            'list' => $result,
            'count' => $this->getBasketItemsCount(),
            'total' => $total,
            'table_number' => \Yii::$app->session->get('table_number'),
        ]);
    }



    /**
     * @SWG\Post(path="/api/basket/delete",
     *     tags={"Basket"},
     *      @SWG\Parameter(
     *      name="address",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Ид адреса"
     *      ),
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
        list($result, $total) = $this->getBasketItems();

        return $this->asJson(
            [
                'status' => 1,
                'error' => 0, 
                'message' => '',
                'list' => $result,
                'count' => $this->getBasketItemsCount(),
                'total' => $total,
                'table_number' => \Yii::$app->session->get('table_number'),
            ]
        );
    }

    /**
     * @SWG\Post(path="/api/basket/clear",
     *     tags={"Basket"},
     *      @SWG\Parameter(
     *      name="address",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description="Ид адреса"
     *      ),
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
    public function actionClear()
    {
        $request = \Yii::$app->request;
        $this->getBasket()->clear();
        list($result, $total) = $this->getBasketItems($request->post('address'));

        return $this->asJson(['status' => 1,  'express_cost' => self::expressCost, 'error' => 0, 'message' => '', 'pledge_price' => self::pledgePrice, 'list' => $result, 'count' => $this->getBasketItemsCount(), 'total' => $total, 'discount' => 0, 'fuser_id' => Fuser::getUserId(), 'time_intervals' => $this->getBasket()->getTimeIntervals($request->post('address'), $result['hasCoolers'])]);
    }

    private function getBasketItemsCount()
    {
        return Basket::find()
            ->joinWith('items')
            ->joinWith('items.product')
            ->where(
                [
                    'table_id' => \Yii::$app->session->get('table_number'),
                    'order_id' => null
                ]
            )
            ->cache(false)
            ->asArray()
            ->count();
    }
}
