<?php

namespace app\Services\api\user_app;

use app\models\tables\Order;
use app\models\tables\Products;
use app\models\tables\Table;
use Exception;

class IikoService extends IikoConfigService
{
    /**
     * Retrieves an order by its ID and table ID.
     *
     * @param int $orderId The ID of the order.
     * @param int $tableId The ID of the table.
     * @throws Exception If the order is not found.
     * @return array The order details.
     */
    private function getOrder(int $orderId, int $tableId): array
    {
        $filter = [
            'orders.table_id' => $tableId,
            'orders.id' => $orderId
        ];

        $order = Order::find()
            ->where($filter)
            ->with([
                'basket.items.product',
                'basket.items.product.productPropertiesValues.property' => function ($query) {
                    $query->andWhere(['code' => 'orderItemType']);
                },
                'paymentType'
            ])
            ->asArray()
            ->one();

        if (!$order) {
            throw new Exception('Order not found');
        }

        return $order;
    }

    /**
     * Prepares the order items based on the given order array.
     *
     * @param array $order The order array containing the basket items.
     * @return array The prepared order items.
     */
    private function prepareOrderItems(array $order): array
    {
        $arItems = [];
        foreach ($order['basket']['items'] as $item) {
            $product = $item['product'];
            $productPropVal = null;

            foreach ($product['productPropertiesValues'] as $propVal) {
                // Find the product property for order item type
                if (isset($propVal['property']) && $propVal['property']['code'] === 'orderItemType') {
                    $productPropVal = $propVal;
                    break;
                }
            }

            // Prepare the order item
            $arItem = [];
            if ($productPropVal && $productPropVal['value'] === 'Compound') {
                $arItem['primaryComponent'] = [
                    'productId' => $product['external_id']
                ];
            }

            $arItem['productId'] = $product['external_id'];
            $arItem['price'] = $item['price'];
            $arItem['type'] = $productPropVal['value'] ?? null;
            $arItem['amount'] = $item['quantity'];
            $arItems[] = $arItem;
        }
        return $arItems;
    }

    /**
     * Prepares the data for an order.
     *
     * @param int $orderId The ID of the order.
     * @return array The prepared data for the order.
     */
    public function prepareDataForOrder(int $orderId): array
    {
        // Get the table
        $table = Table::getTable();

        // Check if the table exists
        if (!$table) {
            throw new Exception('Table not found');
        }

        $order = $this->getOrder($orderId, $table->id);

        // Prepare the order items
        $arItems = $this->prepareOrderItems($order);

        // Check if the payment type is available
        if (!$order['paymentType'] || $order['paymentType']['is_deleted']) {
            throw new Exception('Choosen payment method is unavailable');
        }

        // Prepare the data for the order
        $data = [
            'organizationId' => $this->IIKO_ORG_ID,
            'terminalGroupId' => $this->IIKO_TERMINAL_GROUP_ID,
            'order' => [
                'tableIds' => [
                    0 => (string) $table->external_id
                ],
                'externalNumber',
                'items' => $arItems,
                'payments' => [
                    0 => [
                        'paymentTypeKind' => $order['payment_method'],
                        'sum' => (float) $order['order_sum'],
                        //TODO: убрать //пока заглушка так ка доступна только оплата бонусами да и то она удалена
                        'paymentTypeId' => (string) 'b1d53e5f-81c0-413e-8ad0-ea8d5cc7eb18', //$order['$paymentType']['external_id'],
                        'isProcessedExternally' => (bool) $order['payment_method'] === 'External',
                    ],
                ],
            ]
        ];

        return $data;
    }

    /**
     * Processes the stop list and updates the product balances.
     *
     * @return array An array containing the HTTP status code and the response data.
     */
    public function processStopList(): array
    {
        try {
            $outData = $this->getStopList();
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }

        $items = $outData["terminalGroupStopLists"][0]["items"][0]["items"];
        $productIds = [];
        $productBalances = [];
        foreach ($items as $item) {
            $productIds[] = $item["productId"];
            $productBalances[] = intval(floor($item["balance"]));

            $product = Products::find()
                ->where(['external_id' => $item["productId"]])
                ->one();
            if (!$product) {
                return array(self::HTTP_BAD_REQUEST, 'Product not found');
            }
            $product->balance = intval(floor($item["balance"]));
            if (!$product->save()) {
                return array(self::HTTP_BAD_REQUEST, $product->errors);
            }
        }

        // Products::updateAll(['balance' => $productBalances], ['external_id' => $productIds]);
        //TODO: updateAll почему то инсертит 1 вместо прилетевшего числа. Нужно разобраться

        return array(self::HTTP_OK, $items);
    }

    /**
     * Retrieves the stop list as an array.
     *
     * @return array The stop list data.
     * @throws Exception If an error occurs during the API call.
     */
    private function getStopList(): array
    {
        $token = $this->getKey();

        $url = 'stop_lists';
        $data = [
            'organizationIds' => [$this->IIKO_ORG_ID],
            'returnSize' => true,
            'terminalGroupIds' => $this->IIKO_TERMINAL_GROUP_ID
        ];

        try {
            $outData = $this->gateWay($url, $data, $token);
        } catch (Exception $e) {
            throw $e;
        }
        return $outData;
    }
}
