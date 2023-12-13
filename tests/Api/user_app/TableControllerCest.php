<?php


namespace Api\user_app;

use \ApiTester;

class TableControllerCest
{
    public function testTableCloseWithExistingTableNumber(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendPut('user_app/table/close', ['tableNumber' => 2]);
        $I->seeResponseCodeIs(200);
    }

    public function testTableCloseWithNonExistingTableNumber(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendPut('user_app/table/close', ['tableNumber' => 20]);
        $I->seeResponseCodeIs(404);
    }

    public function testTableCloseWithoutTableNumber(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendPut('user_app/table/close');
        $I->seeResponseCodeIs(400);
    }

    public function testTableCloseWithWrongRequestMethod(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendPost('user_app/table/close');
        $I->seeResponseCodeIs(405);
    }

    public function testTableCloseWithWrongCredentials(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:wrongPassword");
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->sendPost('user_app/table/close');
        $I->seeResponseCodeIs(403);
    }
}
