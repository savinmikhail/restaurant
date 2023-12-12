<?php


namespace Api\user_app;

use \ApiTester;

class OrderControllerCest
{
    public function testIndexAction(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendPost('user_app/order');
        $I->seeResponseCodeIs(200);
    }
}