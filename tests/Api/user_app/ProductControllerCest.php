<?php


namespace Api\user_app;

use \ApiTester;

class ProductControllerCest
{
    public function testIndexActionWithProductNameFilter(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendGET('user_app/products', ['productName' => 'yourProductNameFilter']);
        $I->seeResponseCodeIs(200);
    }
}
