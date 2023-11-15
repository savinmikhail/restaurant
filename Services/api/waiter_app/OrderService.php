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
            ->joinWith('items.product')
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
                    'product_id' => $item['product']['id'],
                    'size_id' => $item['size_id'],
                    'quantity' => $item['quantity'],
                    'id' => $item['id']
                ];
            }, $order['items'])
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
            $orderId = BasketItem::find()->where(['id' => $itemId])->select('order_id')->scalar();

            $updatedCount = BasketItem::updateAll(['quantity' => $quantity], ['id' => $itemId]);
            $this->updateTotal($orderId);
            if ($updatedCount > 0) {
                return array(200, ['data' => 'Item updated successfully']);
            } else {
                return array(400, ['data' => "Item quantity is already $quantity"]);
            }
        } catch (\Exception $e) {
            // \Yii::error("Error updating basket item: " . $e->getMessage(), __METHOD__);
            return array(500, ['data' => 'An error occurred', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    private function updateTotal(int $orderId)
    {
        $order = Order::findOne($orderId);

        if (!$order) {
            throw new \Exception('Order not found for ID: ' . $orderId);
        }
        $orderSum = BasketItem::find()->where(['order_id' => $orderId])->sum('quantity * price') ?? 0;
        $order->order_sum = $orderSum;
        if (!$order->save()) {
            throw new \Exception("Failed to save Order: " . print_r($order->errors, true));
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
            $orderId = BasketItem::find()->where(['id' => $itemId])->select('order_id')->scalar();

            $deletedCount = BasketItem::deleteAll(['id' => $itemId]);
            $this->updateTotal($orderId);

            if ($deletedCount === 1) {
                return array(200, ['data' => 'Item deleted successfully']);
            } else {
                return array(404, ['data' => 'Item not found while deleting']);
            }
        } catch (\Exception $e) {
            // \Yii::error("Error deleting basket item: " . $e->getMessage(), __METHOD__);
            return array(500, ['data' => 'An error occurred', 'error' => $e->getMessage()]);
        }
    }

    /**
     * Adds a new item to the basket.
     *
     * @param int $productId The ID of the product to be added.
     * @param int $sizeId The ID of the size of the product.
     * @param int $orderId The ID of the order to which the item will be added.
     * @param int $quantity The quantity of the product to be added.
     *
     * @return array An array containing the status code and response data.
     */
    public function addNewItem(int $productId, int $sizeId, int $orderId, int $quantity): array
    {
        // Check for invalid input
        if (!$productId || !$sizeId || !$orderId) {
            return [400, ['data' => 'Invalid input']];
        }

        // Find the order
        $order = Order::find()->where(['id' => $orderId])->one();
        if (!$order) {
            return [404, ['data' => 'Order not found']];
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            // Find the basket
            $basket = Basket::findOne($order->basket_id);
            if (!$basket) {
                return [404, ['data' => 'Basket not found']];
            }

            // Add the item to the basket
            $obNewItem = $basket->addItem($productId, $quantity, $sizeId);
            $obNewItem->order_id = $orderId;
            if(!$obNewItem->save()){
                throw new \Exception("Failed to save BasketItem: " . print_r($obNewItem->errors, true));
            }
            
            // Get the updated basket items
            $result = $basket->getBasketItems();

            // Prepare the items for display
            $total = Util::prepareItems($result['items']);

            // Update the order sum
            Order::updateAll(['order_sum' => $total], ['id' => $orderId]);
            $transaction->commit();

            return [200, $result];
        } catch (\Exception $e) {
            $transaction->rollBack();

            // Log the error
            // \Yii::error("Error adding item to basket: " . $e->getMessage(), __METHOD__);

            // Return the error response
            return [500, ['data' => 'An error occurred', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]];
        }
    }
}
