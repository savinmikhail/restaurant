<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\Services\api\user_app\PaymentService;

class PaymentController extends ApiController
{
    private PaymentService $paymentService;

    public function __construct($id, $module, PaymentService $paymentService, $config = [])
    {
        $this->paymentService = $paymentService;
        parent::__construct($id, $module, $config);
    }


    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'pay' => ['POST'],
                    'checkPaid' => ['GET'],
                    'sbpPayTest' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * @SWG\Post(
     *     path="/api/user_app/payment/pay",
     *     tags={"UserApp\Payment"},
     *     @SWG\Parameter(
     *         name="orderId",
     *         in="formData",
     *         type="array",
     *         items=@SWG\Items(
     *             type="integer",
     *             description="order id"
     *         ),
     *     ),
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Ссылка на оплату \ вызов официанта",
     *         @SWG\Schema(ref="#/definitions/Products")
     *     )
     * )
     */
    public function actionPay()
    {
        $orderIds = \Yii::$app->request->post('orderId');

        if (!$orderIds) {
            $this->sendResponse(400, ['data' => "orderId parameter is missing in request"]);
        }
        //сделано для сваггера
        if (is_string($orderIds)) {
            $orderIds = explode(',', $orderIds);
        }

        list($code, $data) = $this->paymentService->pay($orderIds);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Get(path="/api/user_app/payment/check-paid",
     *     tags={"UserApp\Payment"},
     *     @SWG\Parameter(
     *          name="tinkoff_payment_id",
     *          in="query",
     *          type="integer",
     *          description="tinkoff payment id, example: 3554385638"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Подтверждение от СБП",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    /**
     * Check if the payment has been made.
     *
     * @return void
     */
    public function actionCheckPaid()
    {
        $tinkoffPaymentId = \Yii::$app->request->get('tinkoff_payment_id');
        list($code, $data) = $this->paymentService->checkPaid($tinkoffPaymentId);
        $this->sendResponse($code, $data);
    }

    /**
     * @SWG\Post(path="/api/user_app/payment/sbp-pay-test",
     *     tags={"UserApp\Payment"},
     *     @SWG\Parameter(
     *          name="payment_id",
     *          in="formData",
     *          type="integer",
     *          description="tinkoff payment id"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "оплатить по СБП",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionSbpPayTest()
    {
        $paymentId = \Yii::$app->request->post('payment_id');
        list($code, $data) = $this->paymentService->sbpPayTest($paymentId);
        $this->sendResponse($code, $data);
    }
}
