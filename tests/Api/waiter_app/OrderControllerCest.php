<?php


namespace Api\waiter_app;

use \ApiTester;
use Codeception\Attribute\Examples;
use Codeception\Example;
use Codeception\Util\HttpCode;

class OrderControllerCest
{
    private string $authHeader;
    public function _before(ApiTester $I)
    {
        $this->authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $this->authHeader);
    }

    public function testList(ApiTester $I)
    {
        $I->sendGET('waiter_app/orders');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['orders' => [], 'pagination' => []]);
    }

    public function testIndex(ApiTester $I)
    {
        $I->sendGET('waiter_app/order', ['orderId' => 354]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'order_id' => 'integer',
            'order_sum' => 'integer',
            'created_at' => 'string',
            'table_number' => 'integer',
            'items' => 'array',
        ]);
    }

    public function testAdd(ApiTester $I)
    {
        $I->haveHttpHeader('table', 2);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]); //создаю корзину
        $response = $I->sendPost('user_app/order'); //создаю заказ
        $orderId = json_decode($response, true)['orderId'];

        $I->sendPOST('waiter_app/order/add-item', ['product_id' => 63, 'size_id' => 2, 'order_id' => $orderId]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'items' => 'array',
            'id' => 'integer',
        ]);
    }

    public function testEdit(ApiTester $I)
    {
        $I->haveHttpHeader('table', 2);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]); //создаю корзину
        $response = $I->sendPost('user_app/order'); //создаю заказ
        $orderId = json_decode($response, true)['orderId'];
        $resp = $I->sendPOST('waiter_app/order/add-item', ['product_id' => 63, 'size_id' => 2, 'order_id' => $orderId]);
        $itemId = json_decode($resp, true)['items'][0]['id'];

        $I->sendPut('waiter_app/order/edit', ['item_id' => $itemId,'quantity' => 3]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"data":"Item updated successfully"}');
    }

    //не удается протестить делет запросы
    
    // public function testDelete(ApiTester $I)
    // {
    //     $I->haveHttpHeader('table', 2);
    //     $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]); //создаю корзину
    //     $response = $I->sendPost('user_app/order'); //создаю заказ
    //     $orderId = json_decode($response, true)['orderId'];
    //     $resp = $I->sendPOST('waiter_app/order/add-item', ['product_id' => 63, 'size_id' => 2, 'order_id' => $orderId]);
    //     $itemId = json_decode($resp, true)['items'][0]['id'];

    //     $I->sendDelete('waiter_app/order/delete-item', ['item_id' => $itemId]);

    //     $I->seeResponseCodeIs(HttpCode::OK);
    //     $I->seeResponseIsJson();
    //     $I->seeResponseContains('{"data":"Item updated successfully"}');
    // }
}
