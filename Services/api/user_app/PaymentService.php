<?php

namespace app\Services\api\user_app;

use app\models\tables\Basket;
use app\models\tables\Order;
use app\models\tables\Payment;
use app\models\tables\Table;
use app\Services\api\BaseService;
use Exception;
use app\Services\api\user_app\Payment as User_appPayment;
use Yii;

class PaymentService extends BaseService
{
    public function __construct(private User_appPayment $paymentService, private IikoTransportService $iikoTransportService)
    {
    }
    /**
     * Executes a payment for the given order IDs.
     *
     * @param array $orderIds The array of order IDs to be paid.
     * @throws Exception If an error occurs during the payment process.
     * @return array The response array with the status code and the payment result.
     */
    public function pay(array $orderIds): array
    {
        try {
            $transaction = Yii::$app->db->beginTransaction();
            // фронт шлет несколько заказов на оплату, создаю для них платеж
            $payment = $this->createPayment($orderIds);
            //проверяю условия оплаты
            $result = $this->processPaymentMethod($payment);
            //очищаю корзину
            $this->refreshBasket();
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            return [self::HTTP_BAD_REQUEST, ['data' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]];
        }

        return [self::HTTP_OK, $result];
    }

    /**
     * Creates a payment using the provided order IDs.
     *
     * @param array $orderIds An array of order IDs.
     * @throws Exception If no order IDs are provided or if no orders are found.
     * @return Payment The created payment object.
     */
    private function createPayment(array $orderIds): Payment
    {
        $payment = new Payment();

        if (empty($orderIds)) {
            throw new Exception('No order IDs provided');
        }
        $orders = Order::find()->where(['id' => $orderIds])->all();
        if (empty($orders)) {
            throw new Exception('No orders found');
        }

        $orderSum = 0;
        foreach ($orders as $order) {
            $orderSum += $order->order_sum;
        }

        $payment->sum = $orderSum;
        $payment->order_ids = serialize($orderIds);
        $payment->payment_method = 'sbp'; //так как пользователь тыкнул на оплатить куар кодом, пишем этот тип платежа
        if (!$payment->save()) {
            throw new Exception('Failed to save payment: ' . json_encode($payment->errors));
        }
        return $payment;
    }

    /**
     * Process the payment method and return the result as an array.
     *
     * @param Payment $payment The payment object to process.
     * @throws Exception If an error occurs during processing.
     * @return array The processed payment result.
     */
    private function processPaymentMethod(Payment $payment): array
    {
        try {
            list($paymentQr, $tinkoffPaymentId) = $this->paymentService->getTinkoffPaymentQr($payment);
        } catch (Exception $e) {
            throw $e;
        }
        $payment->external_id = $tinkoffPaymentId;
        if (!$payment->save()) {
            throw new Exception('Failed to save payment: ' . json_encode($payment->errors));
        }
        $result = [
            'payment_id' => $payment->id,
            'tinkoff_payment_id' => $tinkoffPaymentId,
            'qr' => $paymentQr,
        ];
        return $result;
    }

    /**
     * Refreshes the basket for the current table.
     *
     * @throws Exception if the table is not found or the basket is not found
     * @return void
     */
    private function refreshBasket()
    {
        //заказ сформирован, отправлен на оплату, удаляю корзину для стола и создаю новую, чтобы очистить корзину от товаров, но оставить товары для заказа
        $table = Table::getTable();
        if (!$table) {
            throw new Exception('Table not found');
        }
        $basket = Basket::find()
            ->where(['table_id' => $table->id])
            ->one();
        if (!$basket) {
            throw new Exception('Basket not found');
        }
        $basket->delete();

        $basket = new Basket();
        $basket->table_id = $table->id;
        if (!$basket->save()) {
            throw new Exception('Failed to save new basket: ' . json_encode($basket->errors));
        }
    }

    /**
     * Checks if a Tinkoff payment is paid.
     *
     * @param int $tinkoffPaymentId The ID of the Tinkoff payment.
     * @throws Exception If an error occurs while checking the payment status.
     * @return array An array with the HTTP code and the payment status data.
     */
    public function checkPaid(int $tinkoffPaymentId): array
    {
        try {
            $status = $this->getStatus($tinkoffPaymentId);

            if ($status !== 'CONFIRMED') {
                return array(self::HTTP_OK, ['data' => $status]);
            }
            $payment = $this->markPaymentAsPaid($tinkoffPaymentId);
            $this->markOrdersAsPaid($payment);
            
            return array(self::HTTP_OK, ['data' => 'OK']);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, ['data' => $e->getMessage()]);
        }
    }

    /**
     * Retrieves the status of a Tinkoff payment.
     *
     * @param int $tinkoffPaymentId The ID of the Tinkoff payment.
     * @throws Exception If there is an error retrieving the payment status.
     * @return string The status of the Tinkoff payment.
     */
    private function getStatus(int $tinkoffPaymentId): string
    {
        try {
            $tinkoffResponse = $this->paymentService->getState($tinkoffPaymentId);
        } catch (Exception $e) {
            throw $e;
        }
        if (!$tinkoffResponse['Success']) {
            throw new Exception($tinkoffResponse['Message']);
        }
        return $tinkoffResponse['Status'];
    }

    /**
     * Marks a payment as paid.
     *
     * @param int $tinkoffPaymentId The Tinkoff payment ID.
     * @throws Exception If the payment is not found or fails to save.
     * @return Payment The updated payment object.
     */
    private function markPaymentAsPaid(int $tinkoffPaymentId): Payment
    {
        $payment = Payment::find()
            ->where(['external_id' => $tinkoffPaymentId])
            ->one();
        if (!$payment) {
            throw new Exception('Payment not found');
        }
        $payment->paid = 1;
        if (!$payment->save()) {
            throw new Exception('Failed to save payment: ' . json_encode($payment->errors));
        }
        return $payment;
    }

    /**
     * Marks the orders as paid for a given payment.
     *
     * @param Payment $payment The payment object.
     * @throws Exception If there is an error in unserializing the order IDs or marking the order as paid in the iiko transport service.
     * @return void
     */
    private function markOrdersAsPaid(Payment $payment): void
    {

        $orderIds = unserialize($payment->order_ids);
        if ($orderIds === false) {
            throw new Exception('Failed to unserialize order IDs');
        }

        Order::updateAll(['paid' => 1, 'payment_method' => $payment->payment_method], ['id' => $orderIds]);
        foreach ($orderIds as $orderId) {
            //помечаем заказ как оплаченный для айко
            list($code, $data) = $this->iikoTransportService->markOrderAsPaid($orderId);
            if ($code !== self::HTTP_OK) {
                throw new Exception($data);
            }
        }
    }

    /**
     * Executes a test payment using the Tinkoff payment service.
     *
     * @param int $tinkoffPaymentId The ID of the Tinkoff payment.
     * @return array Returns an array with the HTTP status code and the payment status.
     */
    public function sbpPayTest(int $tinkoffPaymentId): array
    {
        $tinkoffResponse = $this->paymentService->sbpPayTest($tinkoffPaymentId);
        if ($tinkoffResponse['Success']) {
            return [self::HTTP_OK, ['Paid' => true]];
        }
        return [self::HTTP_BAD_REQUEST, $tinkoffResponse['Message']];
    }
}
