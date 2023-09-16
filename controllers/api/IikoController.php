<?php

namespace app\controllers\api;

use app\controllers\ApiController;
use app\models\tables\Order;
use app\models\tables\PaymentType;
use app\models\tables\ProductsPropertiesValues;
use app\models\tables\Table;
use app\Services\ImportHelper;
use Yii;

class IikoController extends ApiController
{
    const API_KEY = 'fbe5d638-20e';
    const ORG_ID = "884babb3-8dbb-44e4-a446-8f61e502e06f";
    const TERMINAL_GROUP_ID = '40a6ea3e-38b0-e5ee-0184-7476df710064';

    //get auth token from iiko
    public function actionKey()
    {
        $data = ['apiLogin' => self::API_KEY];
        $url = 'https://api-ru.iiko.services/api/1/access_token';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
        ));

        $outData = curl_exec($ch);

        curl_close($ch);

        $outData = json_decode($outData, true);
        Yii::$app->session->set('apiToken', $outData['token']);
        dd($outData['token']);
    }

    //get organization id from iiko
    public function actionId()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            dd('Token not found');
        }
        $url =  'https://api-ru.iiko.services/api/1/organizations';
        $data = '{}';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $outData = curl_exec($ch);

        curl_close($ch);

        $outData = json_decode($outData, true);

        if ($outData) {
            return $this->asJson(['success' => true, 'data' => $outData['organizations'][0]['id']]);
        }
        return $this->asJson(['success' => false, 'data' => []]);
    }

    //get menu from iiko, serialize and  store it in the file
    public function actionMenu()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            dd('Token not found');
        }

        $url = 'https://api-ru.iiko.services/api/1/nomenclature';
        $data = ['organizationId' => self::ORG_ID];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $outData = curl_exec($ch);
        curl_close($ch);
        $outData = json_decode($outData, true);
        if ($outData) {
            file_put_contents('../runtime/logs/menu.txt', serialize($outData));
            dump($outData);
        }
        return 'Token expired';
    }

    //parse menu from the file to the DB
    public function actionExport()
    {
        $menuData = unserialize(file_get_contents('../runtime/logs/menu.txt'));
        //if enabled try - catch block, cant receive the stack trace of the error

        // try {
        $menuParser = new ImportHelper();
        $menuParser->parse($menuData);
        // } catch (Exception $e) {
        //     echo 'Error: ' . $e->getMessage();
        //     exit; 
        // }
    }

    //типы оплаты от айко
    public function actionGetPaymentTypeId()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            dd('Token not found');
        }

        $url = 'https://api-ru.iiko.services/api/1/payment_types';
        $data = ['organizationIds' => [self::ORG_ID]];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $outData = curl_exec($ch);
        curl_close($ch);
        $outData = json_decode($outData, true);
        if (!$outData) {
            $this->actionKey();
            dd("Probably token has been expired");
        }

        ImportHelper::processPaymentTypes($outData['paymentTypes']);

        dd($outData);
    }

    //получаю айди столов от айко
    public function actionGetTableIds()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            dd('Token not found');
        }
        $organizationId = Yii::$app->session->get('organizationId');
        if (!$organizationId) {
            dd('Organization Id not found');
        }
        $url = 'https://api-ru.iiko.services/api/1/reserve/available_restaurant_sections';
        $data = ['terminalGroupIds' => [self::TERMINAL_GROUP_ID]];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $outData = curl_exec($ch);
        curl_close($ch);
        $outData = json_decode($outData, true);

        if (!$outData) {
            $this->actionKey();
            dd("Probably token has been expired");
        }
        ImportHelper::processTables($outData['restaurantSections'][0]['tables']);

        dd($outData);
    }

    //получаю айди терминала
    public function actionTerminal()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            dd('Token not found');
        }

        $url = 'https://api-ru.iiko.services/api/1/terminal_groups';
        $data = ['organizationIds' => [self::ORG_ID]];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $outData = curl_exec($ch);
        curl_close($ch);
        $outData = json_decode($outData, true);
        if (!$outData) {
            $this->actionKey();
            dd("Probably token has been expired");
        }
        dd($outData);
    }

    //создать заказ
    public function actionOrder()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            throw new \Exception('Iiko API token has been expired');
        }

        $request = Yii::$app->request;
        if (!$request->isPost || !$request->post('id')) {
            return $this->asJson(['error' => 'empty request']);
        }

        $data = $this->prepareDataForOrder($request->post('id'));

        $url = 'https://api-ru.iiko.services/api/1/order/create';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $outData = curl_exec($ch);
        curl_close($ch);
        $outData = json_decode($outData, true);
        if ($outData) {
            dd($outData);
        }
        return 'Token expired';
    }
    private function prepareDataForOrder(int $orderId): array
    {
        $table = Table::getTable();
        if (!$table) {
            throw new \Exception('Table is undefined');
        }

        $filter = [
            'orders.table_id' => $table->id,
            'orders.id' => $orderId
        ];

        $order = Order::find()->where($filter)
            ->joinWith('basket')
            ->joinWith('basket.items')
            ->joinWith('basket.items.product')
            ->joinWith('productPropertiesValues.property')
            ->asArray()
            ->one();

        $arItems = [];
        foreach ($order['basket']['items'] as $item) {
            $productPropVal = ProductsPropertiesValues::find()
                ->joinWith('property')
                ->where(['products_properties.code' => 'orderItemType'])
                ->andWhere(['products_properties_values.product_id' => $item['product']['id']])
                ->one();
            if ($productPropVal->value === 'Compound') {
                $arItem['primaryComponent'] = [
                    'productId' => $item['product']['external_id']
                ];
            }
            $arItem['productId'] = $item['product']['external_id'];
            $arItem['price'] = $item['price'];
            $arItem['type'] = $productPropVal->value;
            $arItem['amount'] = $item['quantity'];
            $arItems[] = $arItem;
        }
        $paymentType = PaymentType::find()->where(['payment_type_kind' => $order['payment_method']])->one();

        if(!$paymentType || $paymentType->is_deleted){
            throw new \Exception('Choosen payment method is unavailable');
        }

        $data = [
            'organizationId' => self::ORG_ID,
            'terminalGroupId' => self::TERMINAL_GROUP_ID,
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
                        'paymentTypeId' => (string) 'b1d53e5f-81c0-413e-8ad0-ea8d5cc7eb18', //$paymentType->external_id,    //пока заглушка так ка доступна только оплата бонусами да и то она удалена
                        'isProcessedExternally' => (bool) $order['payment_method'] === 'External' ? true : false,
                    ],
                ],
            ]
        ];
        return $data;
    }
}
