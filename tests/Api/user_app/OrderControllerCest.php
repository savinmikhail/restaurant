<?php

namespace Api\user_app;

use \ApiTester;
use Codeception\Util\HttpCode;
use Codeception\Example;
use Codeception\Attribute\Examples;

class OrderControllerCest
{
    private string $authHeader;

    public function _before(ApiTester $I)
    {
        // $this->setAuthorizationHeader($I);
        $this->authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $this->authHeader);
    }

    #[Examples(
        ['productId' => 62, 'sizeId' => 1, 'responseCode' => HttpCode::OK],
        ['productId' => 62, 'sizeId' => 2, 'responseCode' => HttpCode::BAD_REQUEST] //у данного продукта нет данного размера
    )]
    public function testIndexAction(ApiTester $I, Example $example)
    {
        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => $example['productId'], 'sizeId' => $example['sizeId']]); //создаю корзину

        $I->sendPost('user_app/order');

        $I->seeResponseCodeIs($example['responseCode']);
    }

    #[Examples(
        ['productId' => 62, 'sizeId' => 1, 'responseCode' => HttpCode::OK],
    )]
    public function testCheckListAction(ApiTester $I, Example $example)
    {
        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => $example['productId'], 'sizeId' => $example['sizeId']]); //создаю корзину
        $I->sendPost('user_app/order'); //создаю заказ

        $I->sendGet('user_app/order/list');

        $I->seeResponseCodeIs($example['responseCode']);
    }

    public function testSetOrderGuid(ApiTester $I)
    {
        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]); //создаю корзину
        $response = $I->sendPost('user_app/order'); //создаю заказ
        $orderId = json_decode($response, true)['orderId'];
        $orderGuid = uniqid();

        $response = $I->sendPost('user_app/order/set-order-guid', ['orderId' => $orderId, 'orderGuid' => $orderGuid]); //создаю гуид

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function testMarkOrderAsPaid(ApiTester $I)
    {
        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]); //создаю корзину
        $response = $I->sendPost('user_app/order'); //создаю заказ
        $orderId = json_decode($response, true)['orderId'];
        $orderGuid = uniqid();
        $response = $I->sendPost('user_app/order/set-order-guid', ['orderId' => $orderId, 'orderGuid' => $orderGuid]); //создаю гуид

        $I->sendPost('user_app/order/mark-order-as-paid', ['orderId' => $orderGuid, 'paid' => 1]);

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function testCheckConfirmed(ApiTester $I)
    {
        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]); //создаю корзину
        $response = $I->sendPost('user_app/order'); //создаю заказ
        $orderId = json_decode($response, true)['orderId'];
   
        $I->sendGet('user_app/order/check-confirmed', ['id' => $orderId]);
        
        $I->seeResponseCodeIs(HttpCode::OK);
    }


    public function testCancel(ApiTester $I)
    {
        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]); //создаю корзину
        $response = $I->sendPost('user_app/order'); //создаю заказ
        $orderId = json_decode($response, true)['orderId'];
        $orderGuid = uniqid();
        $response = $I->sendPost('user_app/order/set-order-guid', ['orderId' => $orderId, 'orderGuid' => $orderGuid]); //создаю гуид

        $I->sendPost('user_app/order/cancel', ['orderGuid' => $orderGuid]);

        $I->seeResponseCodeIs(HttpCode::OK);
    }

}
