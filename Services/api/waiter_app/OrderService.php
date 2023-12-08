<?php

namespace app\Services\api\waiter_app;

use app\common\Util;
use app\models\tables\Basket;
use app\models\tables\BasketItem;
use app\models\tables\Order;
use app\models\tables\Products;
use yii\data\Pagination;

class OrderService
{
    /**
     * Retrieves a list of data with pagination.
     *
     * @param int $page The page number to retrieve.
     * @param int $perPage The number of items per page.
     * @return array An array containing the HTTP status code and the data.
     */
    public function getListData(int $page, int $perPage): array
    {
        $query = Order::find()
            ->joinWith('table')
            ->select(['orders.id as order_id', 'tables.table_number'])
            ->andWhere(['canceled' => 0])
            ->andWhere(['paid' => 0])//не показываем оплаченные заказы, чтоб официант не мог отредактировать оплаченный
            ->addOrderBy(['orders.id' => SORT_DESC]);

        // Set up pagination
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $perPage]);
        $pages->setPage($page - 1); // Adjust page number (0 indexed)

        $productsData = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        foreach ($productsData as $index => $order) {
            //без этого куска кода почему-то добавляется лишний ключ table=>null
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

    /**
     * Retrieves the index data for a given order ID.
     *
     * @param int $orderId The ID of the order.
     * @return array The index data for the order.
     */
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
                    'product_balance' => $item['product']['balance'],
                    'size_id' => $item['size_id'],
                    'quantity' => $item['quantity'],
                    'id' => $item['id']
                ];
            }, $order['items'])
        ];
        return [200, $filteredOrderDetails];
    }

    /**
     * Updates the quantity of a basket item.
     *
     * @param int $itemId The ID of the item to update.
     * @param int $quantity The new quantity value.
     * @throws \Exception If an error occurs during the update process.
     * @return array An array containing the HTTP status code and the response data.
     */
    public function updateQuantity(int $itemId, int $quantity): array
    {
        if (!$itemId || !$quantity || $quantity < 1) {
            return array(400, ['data' => 'Invalid input']);
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $item = BasketItem::find()->where(['id' => $itemId])->one();
            if (!$item) {
                return array(404, ['data' => 'Item not found']);
            }
            $orderId = BasketItem::find()->where(['id' => $itemId])->select('order_id')->scalar();

            //закоментировано, так как бизнес логика велит не следить за количеством в стоплисте
            //если с плюсом, то была добавлена новая единица, вычитаю из склада, иначе была удалена позиция, прибавляю на складе
            // $diff = $quantity - $item->quantity;
            // $this->updateProductBalance($item, $diff);

            $updatedCount = BasketItem::updateAll(['quantity' => $quantity], ['id' => $itemId]);
            $this->updateTotal($orderId);
            $transaction->commit();
            if ($updatedCount > 0) {
                return array(200, ['data' => 'Item updated successfully']);
            } else {
                return array(400, ['data' => "Item quantity is already $quantity"]);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return array(400, ['data' => 'An error occurred', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    private function updateProductBalance(BasketItem $item, int $diff)
    {
        $Product = Products::find()
            ->where(['id' => $item->product_id])
            ->one();

        if (!$Product) {
            throw new \Exception("Failed to find Product with id $item->product_id");
        }
        if ($diff > (int)$Product->balance) {//если было запрошено товаров больше, чем осталось на складе
            //TODO: имплементировать крон на постоянное обновление
            throw new \Exception("Not enough products in stock, available only: " . $Product->balance);
        }
        $Product->balance -= $diff;
        if (!$Product->save()) {
            //TODO: надо чтоб запрос кроном из айки не перезаписал это значение, пока не уйдет в заказ
            throw new \Exception("Failed to save Product: " . print_r($Product->errors, true));
        }
    }

    /**
     * Updates the total order sum for a given order ID.
     *
     * @param int $orderId The ID of the order.
     * @throws \Exception If the order is not found or fails to save.
     * @return void
     */
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

    /**
     * Deletes an item from the basket.
     *
     * @param int $itemId The ID of the item to be deleted.
     * @throws \Exception If an error occurs during the deletion process.
     * @return array An array containing the HTTP status code and a message regarding the deletion status.
     */
    public function deleteItem(int $itemId): array
    {
        if (!$itemId || $itemId < 1) {
            return array(400, ['data' => 'Invalid input']);
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $item = BasketItem::find()->where(['id' => $itemId])->asArray()->one();
            if (!$item) {
                return array(404, ['data' => 'Item not found']);
            }
            $orderId = BasketItem::find()->where(['id' => $itemId])->select('order_id')->scalar();

            $deletedCount = BasketItem::deleteAll(['id' => $itemId]);
            $this->updateTotal($orderId);
            //бизнес логика велит не следить за  количеством в стоплисте
            // $this->addProductBalance($item);
            $transaction->commit();
            if ($deletedCount === 1) {
                return array(200, ['data' => 'Item deleted successfully']);
            } else {
                return array(404, ['data' => 'Item not found while deleting']);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return array(400, ['data' => 'An error occurred', 'error' => $e->getMessage()]);
        }
    }

    private function addProductBalance(array $item)
    {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        Products::updateAllCounters(['balance' => +$quantity], ['id' => $productId]);
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
            if (!$obNewItem->save()) {
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
            return [400, ['data' => 'An error occurred', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]];
        }
    }
}
