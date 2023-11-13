<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\models\tables\Order;
use app\models\tables\Table;
use app\Services\ImportHelper;
use Exception;
use Yii;

class IikoController extends ApiController
{
    private string $IIKO_API_KEY;
    private string $IIKO_ORG_ID;
    private string  $IIKO_TERMINAL_GROUP_ID;
    private string $IIKO_BASE_URL;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module,  $config);
        $this->IIKO_API_KEY = $_ENV['IIKO_API_KEY'];
        $this->IIKO_ORG_ID = $_ENV['IIKO_ORG_ID'];
        $this->IIKO_TERMINAL_GROUP_ID = $_ENV['IIKO_TERMINAL_GROUP_ID'];
        $this->IIKO_BASE_URL = $_ENV['IIKO_BASE_URL'];
        $this->IIKO_BASE_URL = $_ENV['IIKO_BASE_URL'];
    }

    private function gateWay($url, $data, $token = null)
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

        return $decodedData;
    }

    private function actionKey()
    {
        $data = ['apiLogin' => $this->IIKO_API_KEY];
        $url = 'access_token';

        $outData = $this->gateWay($url, $data);
        if (!$outData) {
            $this->sendResponse(400, 'Cannot get token');
        }
        Yii::$app->session->set('apiToken', $outData['token']);
    }

    /**
     * @SWG\Get(path="/api/iiko/id",
     *     tags={"Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get organization id from iiko",
     *     ),
     * )
     */
    public function actionId()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            $this->actionKey();//TODO: переделать, так как если токен истек, он все еще есть, просто не валидный, поэтому самообновление не работает. мб поставить крон
            $token = Yii::$app->session->get('apiToken');
        }
        $url =  'organizations';
        $data = '{}';
        $outData = $this->gateWay($url, $data, $token);

        if ($outData) {
            $this->sendResponse(200, $outData['organizations'][0]['id']);
        }
        $this->sendResponse(400,  []);
    }

    /**
     * @SWG\Get(path="/api/iiko/menu",
     *     tags={"Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get menu from iiko, serialize and  store it in the file",
     *     ),
     * )
     */
    public function actionMenu()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            $this->actionKey();
            $token = Yii::$app->session->get('apiToken');
        }
        $url = 'nomenclature';
        $data = ['organizationId' => $this->IIKO_ORG_ID];
        $outData = $this->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        file_put_contents('../runtime/logs/menu.txt', serialize($outData));
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Post(path="/api/iiko/export",
     *     tags={"Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "parse menu from the file to the DB",
     *     ),
     * )
     */
    public function actionExport()
    {
        $menuData = unserialize(file_get_contents('../runtime/logs/menu.txt'));
        $menuParser = new ImportHelper();
        $menuParser->parse($menuData);
        $this->sendResponse(200, 'DB was fullfilled');
    }

    /**
     * @SWG\Post(path="/api/iiko/get-payment-type-id",
     *     tags={"Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the available payment types from iiko and store it",
     *     ),
     * )
     */
    public function actionGetPaymentTypeId()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            $this->actionKey();
            $token = Yii::$app->session->get('apiToken');
        }

        $url = 'payment_types';
        $data = ['organizationIds' => [$this->IIKO_ORG_ID]];
        $outData = $this->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }

        ImportHelper::processPaymentTypes($outData['paymentTypes']);
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Post(path="/api/iiko/get-table-ids",
     *     tags={"Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the table info from iiko and store it",
     *     ),
     * )
     */
    public function actionGetTableIds()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            $this->actionKey();
            $token = Yii::$app->session->get('apiToken');
        }

        $url = 'reserve/available_restaurant_sections';
        $data = ['terminalGroupIds' => [$this->IIKO_TERMINAL_GROUP_ID]];

        $outData = $this->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }

        ImportHelper::processTables($outData['restaurantSections'][0]['tables']);

        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Get(path="/api/iiko/terminal",
     *     tags={"Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the terminal info from the iiko",
     *     ),
     * )
     */
    //получаю айди терминала
    public function actionTerminal()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            $this->actionKey();
            $token = Yii::$app->session->get('apiToken');
        }

        $url = 'terminal_groups';
        $data = ['organizationIds' => [$this->IIKO_ORG_ID]];

        $outData = $this->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Post(path="/api/iiko/order",
     *     tags={"Iiko"},
     *      @SWG\Parameter(
     *      name="orderId",
     *      in="formData",
     *      type="integer"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Отправить заказ в айко",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    //создать заказ
    public function actionOrder()
    {
        $this->actionKey();

        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            $this->actionKey();
            $token = Yii::$app->session->get('apiToken');
        }

        $request = Yii::$app->request;
        if (!$request->isPost || !$request->post('orderId')) {
            $this->sendResponse(400, 'Empty request');
        }

        try {
            $data = $this->prepareDataForOrder($request->post('orderId'));
        } catch (Exception $e) {
            $this->sendResponse(400, $e->getMessage());
        }

        $url = 'order/create';

        $outData = $this->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        $this->sendResponse(200, $outData);
    }

    /**
     * Prepares the data for an order.
     *
     * @param int $orderId The ID of the order.
     * @return array The prepared data for the order.
     */
    private function prepareDataForOrder(int $orderId): array
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
                        'paymentTypeId' => (string) 'b1d53e5f-81c0-413e-8ad0-ea8d5cc7eb18', //$order['$paymentType']['external_id'], //TODO: убрать //пока заглушка так ка доступна только оплата бонусами да и то она удалена
                        'isProcessedExternally' => (bool) $order['payment_method'] === 'External',
                    ],
                ],
            ]
        ];

        return $data;
    }

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

    private function getOrder(int $orderId, $tableId)
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
     * @SWG\Get(path="/api/iiko/get-stop-list",
     *     tags={"Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "получить стоп лист с айко",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionGetStopList()
    {

        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            $this->actionKey();
            $token = Yii::$app->session->get('apiToken');
        }

        $url = 'stop_lists';
        $data = [
            'organizationIds' => [$this->IIKO_ORG_ID],
            'returnSize' => true,
            'terminalGroupIds' => $this->IIKO_TERMINAL_GROUP_ID
        ];

        $outData = $this->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Post(path="/api/iiko/check-in-stop-list",
     *     tags={"Iiko"},
     *      @SWG\Parameter(
     *          name="productId",
     *          required=true,
     *          in="formData",
     *          type="string",
     *          description="bec96548-905b-4311-8e87-87ddfbfa0397"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "ПРоверить, кончился ли товар",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    public function actionCheckInStopList()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            $this->actionKey();
            $token = Yii::$app->session->get('apiToken');
        }

        $url = 'stop_lists/check';
        $data = [
            'organizationId' => $this->IIKO_ORG_ID,
            'terminalGroupId' => $this->IIKO_TERMINAL_GROUP_ID,
            'items' => [
                [
                    'productId' => Yii::$app->request->post('productId'),
                    'price' => 100,
                    'type' => 'Product',
                    'amount' => 1000
                ]

            ]
        ];
        try {
            $outData = $this->gateWay($url, $data, $token);
        } catch (Exception $e) {
            $this->sendResponse(400, $e->getMessage());
        }

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        $this->sendResponse(200, $outData);
    }
}
