<?php


namespace Api;

use \ApiTester;

class ProductControllerCest
{

    public function testIndexActionWithProductNameFilter(ApiTester $I)
    {
        $expectedAuthHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $expectedAuthHeader);
        $I->sendGET('waiter_app/products', ['productName' => 'yourProductNameFilter']);
        $I->seeResponseCodeIs(self::HTTP_OK);
    }
}
