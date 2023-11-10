<?php

namespace app\controllers\api\waiter_app;

use app\common\Util;
use app\controllers\api\user_app\OrderableController;
use app\models\tables\Basket;
use app\models\tables\BasketItem;
use app\models\tables\Order;
use app\models\tables\Setting;
use app\models\tables\Table;

class OrderController extends OrderableController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index'  => ['GET'],
                'pay' => ['POST'],
                'cancel' => ['POST'],
                'list' => ['GET'],
                'callWaiter' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Retrieves a list of orders.
     *
     * @SWG\Get(path="/api/waiter_app/order/list",
     *     tags={"WaiterApp\Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    public function actionList()
    {
        $query = Order::find()
            ->joinWith('table')
            ->select(['orders.id as order_id', 'tables.table_number'])
            ->andWhere(['canceled' => 0])
            ->addOrderBy(['orders.id' => SORT_DESC]);

        $ordersList = $query->asArray()->all();

        foreach ($ordersList as $index => $order) { //без этого куска кода почему-то добавляется лишний ключ table=>null
            unset($ordersList[$index]['table']);
        }

        return $this->asJson(['orders' => $ordersList]);
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

        if (!$orderId || !is_numeric($orderId)) {
            $this->sendResponse(400, ['data' => 'Invalid or missing order ID']);
            return;
        }

        $order = Order::find()
            ->where(['orders.id' => $orderId])
            ->joinWith('table')
            ->joinWith('basket.items.product')
            ->asArray()
            ->one();

        if (!$order) {
            $this->sendResponse(404, ['data' => 'Order not found']);
            return;
        }

        $filteredOrderDetails = [
            'order_id' => $order['id'],
            'order_sum' => $order['order_sum'],
            'created_at' => $order['created_at'],
            'table_number' => $order['table']['table_number'],
            'items' => array_map(function ($item) {
                return [
                    'name' => $item['product']['name'],
                    'quantity' => $item['quantity'],
                    'id' => $item['id']
                ];
            }, $order['basket']['items'])
        ];

        $this->sendResponse(200, ['data' => $filteredOrderDetails]);
    }


    /**
     * @SWG\Put(path="/api/waiter_app/edit",
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
    public function actionSet()
    {
        $request = \Yii::$app->request;
        $itemId = $request->post('item_id');
        $quantity = $request->post('quantity');

        if (!$itemId || !$quantity || $quantity < 1) {
            $this->sendResponse(400, ['data' => 'Invalid input']);
        }

        try {
            $itemExists = BasketItem::find()->where(['id' => $itemId])->exists();
            if (!$itemExists) {
                $this->sendResponse(404, ['data' => 'Item not found']);
            }

            $updatedCount = BasketItem::updateAll(['quantity' => $quantity], ['id' => $itemId]);
            if ($updatedCount > 0) {
                $this->sendResponse(200, ['data' => 'Item updated successfully']);
            } else {
                $this->sendResponse(404, ['data' => 'Item not found while updating quantity']);
            }
        } catch (\Exception $e) {
            \Yii::error("Error updating basket item: " . $e->getMessage(), __METHOD__);
            $this->sendResponse(500, ['data' => 'An error occurred']);
        }
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
    public function actionDelete()
    {
        $itemId = (int) \Yii::$app->request->get('item_id');
        if (!$itemId || $itemId < 1) {
            $this->sendResponse(400, ['data' => 'Invalid input']);
        }
        try {
            $itemExists = BasketItem::find()->where(['id' => $itemId])->exists();
            if (!$itemExists) {
                $this->sendResponse(404, ['data' => 'Item not found']);
            }

            $deletedCount = BasketItem::deleteAll(['id' => $itemId]);
            if ($deletedCount === 1) {
                $this->sendResponse(200, ['data' => 'Item deleted successfully']);
            } else {
                $this->sendResponse(404, ['data' => 'Item not found while deleting']);
            }
        } catch (\Exception $e) {
            \Yii::error("Error deleting basket item: " . $e->getMessage(), __METHOD__);
            $this->sendResponse(500, ['data' => 'An error occurred']);
        }
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
     *          name="size_Id",
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
    public function actionAdd()
    {
        $request = \Yii::$app->request->post();
        $productId = $request['product_id'] ?? null;
        $sizeId = $request['size_Id'] ?? null;
        $orderId = $request['order_id'] ?? null;
        $quantity = 1; // default quantity is 1

        if (!$productId || !$sizeId || !$orderId) {
            $this->sendResponse(400, ['data' => 'Invalid input']);
        }

        $order = Order::find()->where(['id' => $orderId])->one();
        if (!$order) {
            $this->sendResponse(404, ['data' => 'Order not found']);
        }
        try {
            $basket = Basket::findOne($order->basket_id);
            if (!$basket) {
                $this->sendResponse(404, ['data' => 'Basket not found']);
            }

            $basket->addItem($productId, $quantity, $sizeId);
            $result = $basket->getBasketItems();
            $total = Util::prepareItems($result['items']);

            Order::updateAll(['order_sum' => $total], ['id' => $orderId]);
            $this->sendResponse(200, $result);
        } catch (\Exception $e) {
            \Yii::error("Error adding item to basket: " . $e->getMessage(), __METHOD__);
            $this->sendResponse(500, ['data' => 'An error occurred']);
        }
    }
}
