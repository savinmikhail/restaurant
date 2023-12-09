<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\Services\api\user_app\IikoService;
use app\Services\api\user_app\ImportHelper;
use Exception;
use Yii;

class IikoController extends ApiController
{
    private IikoService $iikoService;
    private ImportHelper $importHelper;

    private string $IIKO_ORG_ID;
    private string $IIKO_TERMINAL_GROUP_ID;

    public function __construct($id, $module, IikoService $iikoService, ImportHelper $importHelper, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->iikoService = $iikoService;
        $this->importHelper = $importHelper;
        $this->IIKO_ORG_ID = $_ENV['IIKO_ORG_ID'];
        $this->IIKO_TERMINAL_GROUP_ID = $_ENV['IIKO_TERMINAL_GROUP_ID'];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'list' => ['GET'],
                'index' => ['GET'],
                'edit' => ['PUT'],
                'delete-item' => ['DELETE'],
                'add-item' => ['POST'],
            ],
        ];

        return $behaviors;
    }


    /**
     * @SWG\Get(path="/api/user_app/iiko/id",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get organization id from iiko",
     *     ),
     * )
     */
    public function actionId()
    {
        $token = $this->iikoService->getKey();

        $url = 'organizations';
        $data = '{}';
        $outData = $this->iikoService->gateWay($url, $data, $token);

        if ($outData) {
            $this->sendResponse(200, $outData['organizations'][0]['id']);
        }
        $this->sendResponse(400, []);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko/menu",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get menu from iiko, serialize and  store it in the file",
     *     ),
     * )
     */
    public function actionMenu()
    {
        $token = $this->iikoService->getKey();

        $url = 'nomenclature';
        $data = ['organizationId' => $this->IIKO_ORG_ID];
        $outData = $this->iikoService->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        // Get the absolute path for the file using Yii::getAlias()
        $filePath = Yii::getAlias('@runtime/logs/menu.txt');

        // Write data to the file
        file_put_contents($filePath, serialize($outData));

        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Post(path="/api/user_app/iiko/export",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "parse menu from the file to the DB",
     *     ),
     * )
     */
    public function actionExport()
    {
        $menuData = unserialize(file_get_contents('../runtime/logs/menu.txt'));
        if (!is_array($menuData)) {
            $this->sendResponse(400, 'Contained menu file is empty, please, rerun the Menu Action');
        }
       
        try {
            $this->importHelper->parse($menuData);
        } catch (Exception $e) {
            $this->sendResponse(400, $e->getMessage());
        }
        $this->sendResponse(200, 'DB was fullfilled');
    }

    /**
     * @SWG\Post(path="/api/user_app/iiko/get-payment-type-id",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the available payment types from iiko and store it",
     *     ),
     * )
     */
    public function actionGetPaymentTypeId()
    {
        $token = $this->iikoService->getKey();

        $url = 'payment_types';
        $data = ['organizationIds' => [$this->IIKO_ORG_ID]];
        $outData = $this->iikoService->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }

        ImportHelper::processPaymentTypes($outData['paymentTypes']);
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Post(path="/api/user_app/iiko/get-table-ids",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the table info from iiko and store it",
     *     ),
     * )
     */
    public function actionGetTableIds()
    {
        $token = $this->iikoService->getKey();

        $url = 'reserve/available_restaurant_sections';
        $data = ['terminalGroupIds' => [$this->IIKO_TERMINAL_GROUP_ID]];

        $outData = $this->iikoService->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }

        ImportHelper::processTables($outData['restaurantSections'][0]['tables']);

        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko/terminal",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the terminal info from the iiko",
     *     ),
     * )
     */
    //получаю айди терминала
    public function actionTerminal()
    {
        $token = $this->iikoService->getKey();

        $url = 'terminal_groups';
        $data = ['organizationIds' => [$this->IIKO_ORG_ID]];

        $outData = $this->iikoService->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Post(path="/api/user_app/iiko/order",
     *     tags={"UserApp\Iiko"},
     *      @SWG\Parameter(
     *          name="orderId",
     *          in="formData",
     *          type="integer"
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
        $token = $this->iikoService->getKey();

        $request = Yii::$app->request;
        if (!$request->isPost || !$request->post('orderId')) {
            $this->sendResponse(400, 'Empty request');
        }

        try {
            $data = $this->iikoService->prepareDataForOrder($request->post('orderId'));
        } catch (Exception $e) {
            $this->sendResponse(400, $e->getMessage());
        }

        $url = 'order/create';

        $outData = $this->iikoService->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko/get-stop-list",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "получить стоп лист с айко и обновить записи в бд",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionGetStopList()
    {
        list($code, $data) = $this->iikoService->processStopList();
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Post(path="/api/user_app/iiko/check-in-stop-list",
     *     tags={"UserApp\Iiko"},
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
        $productId = Yii::$app->request->post('productId');
        $token = $this->iikoService->getKey();

        $url = 'stop_lists/check';
        $data = [
            'organizationId' => $this->IIKO_ORG_ID,
            'terminalGroupId' => $this->IIKO_TERMINAL_GROUP_ID,
            'items' => [
                [
                    'productId' => $productId,
                    'price' => 100,
                    'type' => 'Product',
                    'amount' => 1000
                ]

            ]
        ];
        try {
            $outData = $this->iikoService->gateWay($url, $data, $token);
        } catch (Exception $e) {
            $this->sendResponse(400, $e->getMessage());
        }

        if (!$outData) {
            $this->sendResponse(400, 'Failed to retrieve data from Iiko');
        }
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko/get-webhook",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the webhook settings from the iiko",
     *     ),
     * )
     */
    public function actionGetWebhook()
    {
        $token = $this->iikoService->getKey();

        $url = 'webhooks/settings';
        $data = ['organizationId' => $this->IIKO_ORG_ID];

        $outData = $this->iikoService->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        $this->sendResponse(200, $outData);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko/set-webhook",
     *     tags={"UserApp\Iiko"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "set the webhook settings for the iiko",
     *     ),
     * )
     */
    public function actionSetWebhook()
    {
        $token = $this->iikoService->getKey();
        $url = 'webhooks/update_settings';
        $data = [
            'organizationId' => $this->IIKO_ORG_ID,
            'webHooksUri' => 'http://' . $_SERVER['HTTP_HOST'] . '/api/user_app/iiko/webhook',
            'authToken' => md5($_ENV['API_PASSWORD']),
            'webHooksFilter' => [
                'stopListUpdateFilter' => [
                    'updates' => true
                ]
            ]
        ];
        $outData = $this->iikoService->gateWay($url, $data, $token);

        if (!$outData) {
            $this->sendResponse(400, 'Token has been expired');
        }
        $this->sendResponse(200, $outData);
    }

    /**
     * @deprecated version 1.0.0
     * получить инфу, полностью ли обновлен стоплист
     */
    public function actionWebhook()
    {
        // Get the raw POST data
        $postData = file_get_contents('php://input');

        if(empty($postData)) {
            http_response_code(400);
            return;
        }
        $postData = json_decode($postData, true);

        foreach ($postData[0]['eventInfo']['terminalGroupsStopListsUpdates'] as $product) {
            
        }

        //пример ответа
        $jayParsedAry = [
            [
                "eventType" => "StopListUpdate",
                "eventTime" => "2019-08-24 14:15:22.123",
                "organizationId" => "7bc05553-4b68-44e8-b7bc-37be63c6d9e9",
                "correlationId" => "48fb4cd3-2ef6-4479-bea1-7c92721b988c",
                "eventInfo" => [
                    "terminalGroupsStopListsUpdates" => [
                        [
                            "id" => "497f6eca-6276-4993-bfeb-53cbbbba6f08",
                            "isFull" => true
                        ]
                    ]
                ]
            ]
        ];


        // Send a response
        http_response_code(200);
    }
}
