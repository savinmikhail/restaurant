<?php


namespace Api\waiter_app;

use \ApiTester;

class ProductControllerCest
{

    public function testIndexActionWithProductNameFilter(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendGET('waiter_app/products', ['productName' => 'yourProductNameFilter']);
        $I->seeResponseCodeIs(200);
    }

    public function testIndexActionWithPaginationParams(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendGET('waiter_app/products', ['page' => '1', 'perPage' => '10']);
        $I->seeResponseCodeIs(200);
    }
}
