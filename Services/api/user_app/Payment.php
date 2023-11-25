<?php

namespace app\Services\api\user_app;

use app\models\tables\Payment as TablesPayment;

class Payment
{
    private string $TINKOFF_TERMINAL_KEY;
    private string $TINKOFF_SECRET_KEY;

    public function __construct()
    {
        $this->TINKOFF_TERMINAL_KEY = $_ENV['TINKOFF_TERMINAL_KEY'];
        $this->TINKOFF_SECRET_KEY = $_ENV['TINKOFF_SECRET_KEY'];
    }

    public static function createSberPaymentUrl($last_order_id, $amount)
    {
        return [
            1,
            'https://smth.smth',
            []
        ];
        /* $returnUrl = "https://mp.kv.tomsk.ru/api/payment/redirect?orderId={$last_order_id}&success=true";
         $failUrl = "https://mp.kv.tomsk.ru/api/payment/redirect?orderId={$last_order_id}&success=false";
         $host = 'https://mp.kv.tomsk.ru/api/payment/callback';
         //$token = 'auaclh27bt0pejpl05pf138f0';
         $token = 'l08ccs6936bvhai03i5p2fu8fr'; //prod
         $amount = round($amount * 100, 0);
         $arrContextOptions = [
         'ssl' => [
         'verify_peer' => false,
         'verify_peer_name' => false,
         ],
         ];
         //$url = 'https://3dsec.sberbank.ru/payment/rest/register.do'
         $url = 'https://securepayments.sberbank.ru/payment/rest/register.do' //prod
         . "?amount={$amount}&orderNumber=M{$last_order_id}"
         . '&token=' . $token
         // .'&dynamicCallbackUrl='.$host
         . '&pageView=MOBILE'
         . '&returnUrl=' . $returnUrl
         . '&failUrl=' . $failUrl;
         $response = json_decode(file_get_contents($url, false, stream_context_create($arrContextOptions)));

         if (isset($response->formUrl)) {
         return [
         1,
         $response->formUrl,
         []
         ];
         } else {
         return [
         0,
         [
         'code' => $response->errorCode,
         'message' => $response->errorMessage,
         ],
         ['url' => $url]
         ];
         }*/
    }

    /**
     * Process the payment using Tinkoff API.
     *
     * @param TablesPayment $payment The payment object.
     * @throws \Exception Error initializing payment or getting QR code.
     * @return string The URL for the payment QR code, if successfully obtained.
     */
    public function getTinkoffPaymentUrl(TablesPayment $payment): array
    {
        try {
            $tinkoff  = $this->initializePayment($payment);
            $arrResponse = (array)json_decode(htmlspecialchars_decode($tinkoff->response));

            // Check if the initialization was successful
            if (!$arrResponse['Success']) {
                throw new \Exception('Payment was not confirmed: ' . print_r($tinkoff->response, true));
            }
            return [$this->getPaymentUrl($tinkoff), $tinkoff->paymentId];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function initializePayment(TablesPayment $payment): Tinkoff
    {
        $tinkoff = new Tinkoff($this->TINKOFF_TERMINAL_KEY, $this->TINKOFF_SECRET_KEY);

        //Initialize the payment
        $initArgs = [
            'OrderId' => $payment->id,
            'Amount' => $payment->sum . '.00',
            'IP' => $_SERVER['REMOTE_ADDR'], //https://www.tinkoff.ru/kassa/dev/payments/#tag/Standartnyj-platyozh/paths/~1Init/post
        ];

        try {
            $tinkoff->init($initArgs);
        } catch (\yii\web\HttpException $e) {
            throw new \Exception('Error initializing payment: ' . $e->getMessage());
        }
        return $tinkoff;
    }

    private function getPaymentUrl(Tinkoff $tinkoff): string
    {
        // Get the QR code
        $qrArgs = [
            'PaymentId' => $tinkoff->paymentId,
        ];

        try {
            $tinkoff->getQr($qrArgs);
        } catch (\yii\web\HttpException $e) {
            throw new \Exception('Error getting QR code: ' . $e->getMessage());
        }
        $arrResponse = (json_decode(htmlspecialchars_decode($tinkoff->response), true));

        // Check if the QR code was obtained successfully
        if ($arrResponse['Success']) {
            // $tinkoff->paymentUrl now contains the URL for the payment QR code
            return $arrResponse['Data'];
        } else {
            // Handle the case where getting the QR code was not successful
            throw new \Exception('Error getting QR code: ' . $tinkoff->error);
        }
    }

    public function getState(int $paymentId): array
    {
        $tinkoff = new Tinkoff($this->TINKOFF_TERMINAL_KEY, $this->TINKOFF_SECRET_KEY);
        $stateArgs = [
            'PaymentId' => $paymentId,
        ];

        $tinkoff->getState($stateArgs);
        $arrResponse = json_decode(htmlspecialchars_decode($tinkoff->response), true);
        return ($arrResponse); //CONFIRMED
    }

    public function sbpPayTest(int $paymentId): array
    {

        $tinkoff = new Tinkoff($this->TINKOFF_TERMINAL_KEY, $this->TINKOFF_SECRET_KEY);
        $testArgs = [
            'PaymentId' => $paymentId,
            // 'IsDeadlineExpired' => true,
            // 'IsRejected' => true
        ];
        $tinkoff->sbpPayTest($testArgs);
        $arrResponse = json_decode(htmlspecialchars_decode($tinkoff->response), true);
        return ($arrResponse);
    }
}
