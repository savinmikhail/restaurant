<?php

namespace app\commands;

use app\models\tables\Payment;
use app\Services\api\user_app\PaymentService;
use Yii;
use yii\db\Expression;

class CheckPaymentStatusController extends \yii\console\Controller
{
    private PaymentService $paymentService;

    public function __construct($id, $module, PaymentService $paymentService, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->paymentService = $paymentService;
    }

    public function actionIndex()
    {
        Yii::error("run command", 'checkPaymentStatus');//todo: не работает info, логировать в отдельный файл
        try {
            $lastHour = new Expression('NOW() - INTERVAL 1 HOUR');

            $payments = Payment::find()
                ->where(['paid' => 0])
                ->andWhere(['>=', 'created_at', $lastHour])
                ->all();
            if (!$payments) {
                Yii::error("No payments found.", 'checkPaymentStatus');
                return;
            }
            foreach ($payments as $payment) {
                list($code, $data) = $this->paymentService->checkPaid($payment->external_id);
                if ($code == 200) {
                    Yii::error("Payment with ID $payment->id processed successfully.", 'checkPaymentStatus');
                } else {
                    Yii::error("Error processing payment with ID $payment->id:" . print_r($data, true), 'checkPaymentStatus');
                }
            }
        } catch (\Exception $e) {
            // Log error
            Yii::error("Error processing payments: {$e->getMessage()}", 'checkPaymentStatus');
        }
    }
}
