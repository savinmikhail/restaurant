<?php


namespace Api\user_app;

use \ApiTester;
use app\tests\fixtures\BasketFixture;

class OrderControllerCest
{
    public function testIndexAction(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]);//создаю корзину
        $I->sendPost('user_app/order');
        $I->seeResponseCodeIs(200);
    }
    
}
