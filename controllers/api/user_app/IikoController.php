<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\Services\api\user_app\IikoService;
use app\Services\api\user_app\MenuParser;
use app\Services\api\user_app\import_helpers\PaymentTypeHelper;
use app\Services\api\user_app\import_helpers\TableHelper;
use Exception;
use Yii;

class IikoController extends ApiController
{
    private string $IIKO_ORG_ID;
    private string $IIKO_TERMINAL_GROUP_ID;

    public function __construct($id, $module, private IikoService $iikoService, private MenuParser $menuParser, $config = [])
    {
        parent::__construct($id, $module, $config);
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
            $this->menuParser->parse($menuData);
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
        $paymentTypeHelper = new PaymentTypeHelper;
        $paymentTypeHelper->process($outData);
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
        $tableHelper = new TableHelper;
        $tableHelper->process($outData['restaurantSections'][0]);

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
     *         description = "Проверить, кончился ли товар",
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

}
