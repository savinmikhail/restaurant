<?php

namespace app\Services\api\user_app;

use app\models\tables\Order;
use app\models\tables\Products;
use app\models\tables\Table;
use Exception;
use Yii;

class IikoService
{
    private string $IIKO_API_KEY;
    private string $IIKO_ORG_ID;
    private string $IIKO_TERMINAL_GROUP_ID;
    private string $IIKO_BASE_URL;

    public function __construct()
    {
        $this->IIKO_API_KEY = $_ENV['IIKO_API_KEY'];
        $this->IIKO_ORG_ID = $_ENV['IIKO_ORG_ID'];
        $this->IIKO_TERMINAL_GROUP_ID = $_ENV['IIKO_TERMINAL_GROUP_ID'];
        $this->IIKO_BASE_URL = $_ENV['IIKO_BASE_URL'];
    }

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
     * Sends a request to the specified URL using the HTTP POST method and returns the response.
     *
     * @param string $url The URL to send the request to.
     * @param array $data The data to be sent in the request body.
     * @param string|null $token The authorization token to include in the request header. Defaults to null.
     * @throws Exception If there is an error executing the cURL request.
     * @return array The decoded response data from the server.
     */
    public function gateWay($url, $data, $token = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->IIKO_BASE_URL . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $headers = ["Content-Type: application/json"];
        if ($token) {
            $headers[] = "Authorization: Bearer " . $token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $outData = curl_exec($ch);
        if ($outData === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("HTTP Error: Status code $httpCode \n" . print_r(json_decode($outData, true), true));
        }

        $decodedData = json_decode($outData, true);
        if ($decodedData === null && json_last_error() != JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }

        if (!$decodedData) {
            throw new Exception("Feailed to retrieve data from Iiko");
        }

        return $decodedData;
    }

    /**
     * Retrieves the API token.
     *
     * This function retrieves the API token from the session. If the token is not set or has expired,
     * it refreshes the token by sending a request to the access_token endpoint. If the refresh is successful,
     * it updates the token and its expiration timestamp in the session and returns the new token.
     *
     * @return string The API token
     * @throws Exception If the token cannot be refreshed
     */
    public function getKey(): string
    {
        $session = Yii::$app->session;
        $expirationTimestamp = $session->get('apiTokenExpiration');
        $apiToken = $session->get('apiToken');

        // Check if the token is not set or has expired
        if (!$apiToken || !$expirationTimestamp || $expirationTimestamp <= time()) {
            // Token is not set or has been expired, refresh it
            $data = ['apiLogin' => $this->IIKO_API_KEY];
            $url = 'access_token';

            $outData = $this->gateWay($url, $data);
            if (!$outData || !isset($outData['token'])) {
                throw new Exception('Cannot refresh token');
            }

            // Set the new token and its expiration timestamp in the session
            $apiToken = $outData['token'];
            $expirationTimestamp = time() + 60 * 60; // standart life time of token is 1 hour due to documentation
            $session->set('apiToken', $apiToken);
            $session->set('apiTokenExpiration', $expirationTimestamp);
        }
        return $apiToken;
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
            return array(400, $e->getMessage());
        }

        $items = $outData["terminalGroupStopLists"][0]["items"][0]["items"];
        foreach ($items as $item) {
            $productIds[] = $item["productId"];
            $productBalances[] = intval(floor($item["balance"]));

            $product = Products::find()
                ->where(['external_id' => $item["productId"]])
                ->one();
            if (!$product) {
                return array(400, 'Product not found');
            }
            $product->balance = intval(floor($item["balance"]));
            if (!$product->save()) {
                return array(400, $product->errors);
            }
        }

        // Products::updateAll(['balance' => $productBalances], ['external_id' => $productIds]);
        //TODO: updateAll почему то инсертит 1 вместо прилетевшего числа. Нужно разобраться

        return array(200, $items);
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
