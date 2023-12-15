<?php


namespace Api\user_app;

use \ApiTester;
use Codeception\Util\HttpCode;

class ProductControllerCest
{
    private string $authHeader;
    public function _before(ApiTester $I)
    {
        $this->authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $this->authHeader);
    }

    public function testIndexActionWithProductNameFilter(ApiTester $I)
    {
        $I->sendGET('user_app/products', ['productName' => 'yourProductNameFilter']);
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
