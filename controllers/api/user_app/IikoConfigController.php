<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\Services\api\user_app\IikoConfigService;

class IikoConfigController extends ApiController
{

    public function __construct($id, $module, private IikoConfigService $iikoConfigService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko-config/id",
     *     tags={"UserApp\IikoConfig"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get organization id from iiko",
     *     ),
     * )
     */
    public function actionId()
    {
        list($code, $data) = $this->iikoConfigService->getOrganizationId();
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiiko-configiko/terminal",
     *     tags={"UserApp\IikoConfig"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the terminal info from the iiko",
     *     ),
     * )
     */
    //получаю айди терминала
    public function actionTerminal()
    {
        list($code, $data) = $this->iikoConfigService->getTerminalId();
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko-config/get-webhook",
     *     tags={"UserApp\IikoConfig"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "get the webhook settings from the iiko",
     *     ),
     * )
     */
    public function actionGetWebhook()
    {
        list($code, $data) = $this->iikoConfigService->getWebhook();
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko-config/set-webhook",
     *     tags={"UserApp\IikoConfig"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "set the webhook settings for the iiko",
     *     ),
     * )
     */
    public function actionSetWebhook()
    {
        list($code, $data) = $this->iikoConfigService->setWebhook();
        $this->sendResponse($code, $data);
    }

    /**
     * @deprecated version 1.0.0
     * получить инфу, полностью ли обновлен стоплист
     */
    public function actionWebhook()
    {
        // Get the raw POST data
        $postData = file_get_contents('php://input');

        if (empty($postData)) {
            http_response_code(400);
            return;
        }
        $postData = json_decode($postData, true);

        // foreach ($postData[0]['eventInfo']['terminalGroupsStopListsUpdates'] as $product) {
        // }

        //пример ответа
        // $jayParsedAry = [
        //     [
        //         "eventType" => "StopListUpdate",
        //         "eventTime" => "2019-08-24 14:15:22.123",
        //         "organizationId" => "7bc05553-4b68-44e8-b7bc-37be63c6d9e9",
        //         "correlationId" => "48fb4cd3-2ef6-4479-bea1-7c92721b988c",
        //         "eventInfo" => [
        //             "terminalGroupsStopListsUpdates" => [
        //                 [
        //                     "id" => "497f6eca-6276-4993-bfeb-53cbbbba6f08",
        //                     "isFull" => true
        //                 ]
        //             ]
        //         ]
        //     ]
        // ];


        // Send a response
        http_response_code(200);
    }
}
