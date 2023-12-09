<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\Services\api\user_app\IikoTransportService;
use Yii;

class IikoTransportController extends ApiController
{
    private IikoTransportService $iikoTransportService;

    public function __construct($id, $module, IikoTransportService $iikoTransportService, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->iikoTransportService = $iikoTransportService;
    }

    /**
     * @SWG\Post(path="/api/user_app/iiko-transport/order",
     *     tags={"UserApp\IikoTransport"},
     *      @SWG\Parameter(
     *          name="orderId",
     *          in="formData",
     *          type="integer"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "get organization id from iiko",
     *     ),
     * )
     */
    public function actionOrder()
    {
        $request = Yii::$app->request;
        if (!$request->isPost || !$request->post('orderId')) {
            $this->sendResponse(400, 'Empty request');
        }
        $orderId = $request->post('orderId');
        list($code, $data) = $this->iikoTransportService->sendOrder($orderId);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Get(path="/api/user_app/iiko-transport/get-stop-list",
     *     tags={"UserApp\IikoTransport"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "получить стоп лист с айко и обновить записи в бд",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionGetStopList()
    {
        list($code, $data) = $this->iikoTransportService->processStopList();
        $this->sendResponse($code, $data);
    }
}
