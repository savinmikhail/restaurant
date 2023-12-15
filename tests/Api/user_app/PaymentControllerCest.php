<?php


namespace Api\user_app;

use \ApiTester;
use Codeception\Util\HttpCode;
use Codeception\Example;
use Codeception\Attribute\Examples;

class PaymentControllerCest
{
    private string $authHeader;

    public function _before(ApiTester $I)
    {
        $this->authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $this->authHeader);
    }

    public function testPay(ApiTester $I)
    {
        $I->haveHttpHeader('table', 2);


        $I->sendPost('user_app/payment/pay', ['orderId' => 354]);

        $I->seeResponseMatchesJsonType([
            'payment_id' => 'integer',
            'tinkoff_payment_id' => 'integer',
            'qr' => 'string',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function testCheckPaid(ApiTester $I)
    {
        $I->haveHttpHeader('table', 2);
        $response = $I->sendPost('user_app/payment/pay', ['orderId' => 354]);
        $response = json_decode($response, true);

        $I->sendGet('user_app/payment/check-paid', ['tinkoff_payment_id' => $response['tinkoff_payment_id']]);
        $I->seeResponseMatchesJsonType([
            'data' => 'string',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
