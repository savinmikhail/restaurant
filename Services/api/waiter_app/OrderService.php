<?php

namespace app\Services\api\waiter_app;

use app\common\Util;
use app\models\tables\Basket;
use app\models\tables\BasketItem;
use app\models\tables\Order;
use yii\data\Pagination;

class OrderService
{
    public function getListData(int $page, int $perPage): array
    {
        $query = Order::find()
            ->joinWith('table')
            ->select(['orders.id as order_id', 'tables.table_number'])
            ->andWhere(['canceled' => 0])
            ->addOrderBy(['orders.id' => SORT_DESC]);

        // Set up pagination
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $perPage]);
        $pages->setPage($page - 1); // Adjust page number (0 indexed)

        $productsData = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        foreach ($productsData as $index => $order) { //без этого куска кода почему-то добавляется лишний ключ table=>null
            unset($productsData[$index]['table']);
        }

        $output = [
            'orders' => $productsData,
            'pagination' => [
                'totalCount' => $pages->totalCount,
                'page' => $pages->page + 1,
                'perPage' => $pages->pageSize,
            ],
        ];
        return [200, $output];
    }

    public function getIndexData(int $orderId): array
    {
        if (!$orderId || !is_numeric($orderId)) {
            return array(400, ['data' => 'Invalid or missing order ID']);
        }

        $order = Order::find()
            ->where(['orders.id' => $orderId])
            ->joinWith('table')
            ->joinWith('basket.items.product')
            ->asArray()
            ->one();

        if (!$order) {
            return array(404, ['data' => 'Order not found']);
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
        return [200, $filteredOrderDetails];
    }

    public function updateQuantity(int $itemId, int $quantity): array
    {
        if (!$itemId || !$quantity || $quantity < 1) {
            return array(400, ['data' => 'Invalid input']);
        }

        try {
            $itemExists = BasketItem::find()->where(['id' => $itemId])->exists();
            if (!$itemExists) {
                return array(404, ['data' => 'Item not found']);
            }

            $updatedCount = BasketItem::updateAll(['quantity' => $quantity], ['id' => $itemId]);
            if ($updatedCount > 0) {
                return array(200, ['data' => 'Item updated successfully']);
            } else {
                return array(404, ['data' => 'Item not found while updating quantity']);
            }
        } catch (\Exception $e) {
            \Yii::error("Error updating basket item: " . $e->getMessage(), __METHOD__);
            return array(500, ['data' => 'An error occurred']);
        }
    }

    public function deleteItem(int $itemId): array
    {
        if (!$itemId || $itemId < 1) {
            return array(400, ['data' => 'Invalid input']);
        }
        try {
            $itemExists = BasketItem::find()->where(['id' => $itemId])->exists();
            if (!$itemExists) {
                return array(404, ['data' => 'Item not found']);
            }

            $deletedCount = BasketItem::deleteAll(['id' => $itemId]);
            if ($deletedCount === 1) {
                return array(200, ['data' => 'Item deleted successfully']);
            } else {
                return array(404, ['data' => 'Item not found while deleting']);
            }
        } catch (\Exception $e) {
            \Yii::error("Error deleting basket item: " . $e->getMessage(), __METHOD__);
            return array(500, ['data' => 'An error occurred']);
        }
    }

    public function addNewItem(int $productId, int $sizeId, int $orderId, int $quantity): array
    {
        if (!$productId || !$sizeId || !$orderId) {
            return array(400, ['data' => 'Invalid input']);
        }

        $order = Order::find()->where(['id' => $orderId])->one();
        if (!$order) {
            return array(404, ['data' => 'Order not found']);
        }
        try {
            $basket = Basket::findOne($order->basket_id);
            if (!$basket) {
                return array(404, ['data' => 'Basket not found']);
            }

            $basket->addItem($productId, $quantity, $sizeId);
            $result = $basket->getBasketItems();
            $total = Util::prepareItems($result['items']);

            Order::updateAll(['order_sum' => $total], ['id' => $orderId]);
            return array(200, $result);
        } catch (\Exception $e) {
            \Yii::error("Error adding item to basket: " . $e->getMessage(), __METHOD__);
            return array(500, ['data' => 'An error occurred']);
        }
    }
}
