<?php

namespace app\Services\api\user_app;

use app\models\tables\Order;
use app\models\tables\Products;
use app\models\tables\Table;
use Exception;

class IikoTransportService
{
    private string $IIKO_TRANSPORT_IP;

    public function __construct()
    {
        $this->IIKO_TRANSPORT_IP = $_ENV['IIKO_TRANSPORT_IP'];
    }

    /**
     * Marks an order as paid.
     *
     * @param int $orderId The ID of the order to mark as paid.
     * @throws Exception If there is an error marking the order as paid.
     * @return array An array with the HTTP status code and the response from the gateway.
     */
    public function markOrderAsPaid(int $orderId): array
    {
        try {
            $orderGuid = Order::find()->where(['id' => $orderId])->one()->external_id;
            $response =  $this->gateWay('markOrderAsPaid', ['orderId' => $orderGuid, 'paid' => true]);
        } catch (Exception $e) {
            return [400, $e->getMessage()];
        }
        return [200, $response];
    }

    /**
     * Call the waiter and return the response.
     *
     * @return array The response from calling the waiter.
     */
    public function callWaiter(): array
    {
        try {
            $response =  $this->gateWay('callWaiter', ['tableNumber' => Table::getTable()->table_number], 'GET');
        } catch (Exception $e) {
            return [400, $e->getMessage()];
        }
        return [200, $response];
    }

    /**
     * Sends an order and returns the response.
     *
     * @param int $orderId The ID of the order to be sent.
     * @throws Exception If there is an error while sending the order.
     * @return array An array containing the status code and the response.
     */
    public function sendOrder(int $orderId): array
    {
        $data = $this->getOrderData($orderId);
        try {
            $response =  $this->gateWay('SendOrder', $data);
        } catch (Exception $e) {
            return array(400, $e->getMessage());
        }
        return array(200, $response);
    }

    /**
     * Process the stop list.
     *
     * @throws Exception if an error occurs while processing the stop list.
     * @return array An array containing the HTTP status code and the response from the gateway.
     */
    public function processStopList()
    {
        try {
            $response =  $this->gateWay('Stop', [], 'GET');
        } catch (Exception $e) {
            return [400, $e->getMessage()];
        }

        $keys = array_column($response[0], 'Key');
        $values = array_column($response[0], 'Value');

        Products::updateAll(['balance' => $values], ['code' => $keys]);

        return [200, $response];
    }

    /**
     * Retrieves the order data for a given order ID.
     *
     * @param int $orderId The ID of the order.
     * @return array The order data.
     */
    private function getOrderData(int $orderId): array
    {
        $order = Order::find()
            ->where(['orders.id' => $orderId])
            ->joinWith('table')
            ->joinWith('items.size')
            ->joinWith('items.product')
            ->asArray()
            ->one();
        $data = [];
        $data['OrderId'] = $order['id'];
        $data['Table'] = $order['table']['table_number'];
        $data['SummPayment'] = $order['order_sum'];
        foreach ($order['items'] as $item) {
            $data['JsonProducts'][] = [
                "Number" => $item['product']['code'],
                "Product_amount" => $item['quantity'],
                "NameSize" => $item['size']['name'],
                "JsonModifys" => []
            ];
        }
        return $data;
    }

    /**
     * Sends a request to the specified URL using cURL and returns the response data.
     *
     * @param string $url The URL to send the request to.
     * @param array $data The data to send with the request.
     * @param string $method The HTTP method to use for the request. Default is 'POST'.
     * @throws Exception If there is a cURL error or an HTTP error response.
     * @return mixed The decoded response data from the request.
     */
    private function gateWay($url, $data, $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->IIKO_TRANSPORT_IP . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            if(!empty($data)){
                curl_setopt($ch, CURLOPT_URL, $this->IIKO_TRANSPORT_IP . $url . '?' . http_build_query($data));
            }
        }

        $headers = ["Content-Type: application/json"];
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
}
