<?php


namespace Api\user_app;

use \ApiTester;
use Codeception\Util\HttpCode;

class TableControllerCest
{
    private string $authHeader;
    public function _before(ApiTester $I)
    {
        $this->authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $this->authHeader);
    }

    public function testTableCloseWithExistingTableNumber(ApiTester $I)
    {
        $I->sendPut('user_app/table/close', ['tableNumber' => 2]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function testTableCloseWithNonExistingTableNumber(ApiTester $I)
    {
        $I->sendPut('user_app/table/close', ['tableNumber' => 20]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function testTableCloseWithoutTableNumber(ApiTester $I)
    {
        $I->sendPut('user_app/table/close');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function testTableCloseWithWrongRequestMethod(ApiTester $I)
    {
        $I->sendPost('user_app/table/close');
        $I->seeResponseCodeIs(HttpCode::METHOD_NOT_ALLOWED);
    }
}
