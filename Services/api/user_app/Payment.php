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
            throw $e;
        }
    }

    /**
     * Initializes a payment using the Tinkoff payment gateway.
     *
     * documentation: https://www.tinkoff.ru/kassa/dev/payments/#tag/Standartnyj-platyozh/paths/~1Init/post
     * 
     * @param TablesPayment $payment The payment object containing the payment details.
     * @throws \Exception If there is an error initializing the payment.
     * @return Tinkoff The Tinkoff payment object.
     */
    private function initializePayment(TablesPayment $payment): Tinkoff
    {
        $tinkoff = new Tinkoff($this->TINKOFF_TERMINAL_KEY, $this->TINKOFF_SECRET_KEY);

        //Initialize the payment
        $initArgs = [
            'OrderId' => $payment->id,
            'Amount' => $payment->sum . '.00',
            'IP' => $_SERVER['REMOTE_ADDR'], 
        ];

        try {
            $tinkoff->init($initArgs);
        } catch (\yii\web\HttpException $e) {
            throw new \Exception('Error initializing payment: ' . $e->getMessage());
        }
        return $tinkoff;
    }

    /**
     * Retrieves the payment URL for the Tinkoff API.
     *
     * @param Tinkoff $tinkoff The Tinkoff object used to make the API call.
     * @throws \Exception Error getting QR code: [exception message]
     * @return string The URL for the payment QR code.
     */
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

    /**
     * Retrieves the state of a Tinkoff payment.
     *
     * @param int $tinkoffPaymentId The ID of the Tinkoff payment.
     * @return array The state of the Tinkoff payment.
     */
    public function getState(int $tinkoffPaymentId): array
    {
        $tinkoff = new Tinkoff($this->TINKOFF_TERMINAL_KEY, $this->TINKOFF_SECRET_KEY);
        $stateArgs = [
            'PaymentId' => $tinkoffPaymentId,
        ];

        $tinkoff->getState($stateArgs);
        $arrResponse = json_decode(htmlspecialchars_decode($tinkoff->response), true);
        return ($arrResponse); //CONFIRMED
    }

    /**
     * Generates a function comment for the given function body.
     *
     * @param int $tinkoffPaymentId The payment ID for the Tinkoff payment.
     * @return array The response array from the Tinkoff API.
     */
    public function sbpPayTest(int $tinkoffPaymentId): array
    {

        $tinkoff = new Tinkoff($this->TINKOFF_TERMINAL_KEY, $this->TINKOFF_SECRET_KEY);
        $testArgs = [
            'PaymentId' => $tinkoffPaymentId,
            // 'IsDeadlineExpired' => true,
            // 'IsRejected' => true
        ];
        $tinkoff->sbpPayTest($testArgs);
        $arrResponse = json_decode(htmlspecialchars_decode($tinkoff->response), true);
        return ($arrResponse);
    }
}
