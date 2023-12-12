<?php


namespace Api;

use \ApiTester;

class ApiControllerCest
{

    public function testApiAccessWithRightCredentials(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendGET('waiter_app/products', ['productName' => 'yourProductNameFilter']);
        $I->seeResponseCodeIs(200);
    }

    public function testApiAccessWithWrongCredentials(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:wrongPassword");
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendGET('waiter_app/products', ['productName' => 'yourProductNameFilter']);
        $I->seeResponseCodeIs(403);
    }

}
