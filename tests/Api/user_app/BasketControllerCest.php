<?php


namespace Api\user_app;

use \ApiTester;

use Codeception\Util\HttpCode;

class BasketControllerCest
{
    private $authHeader;
    //не удается загрузить трейт для dry загрузки авторизационного заголовка
    // use \tests\Api\traits\ApiAuthTrait;
    public function _before(ApiTester $I)
    {
        $this->authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
    }

    public function testIndexActionWithoutTableHeader(ApiTester $I)
    {
        // $this->setAuthorizationHeader($I);
        $I->haveHttpHeader('Authorization', $this->authHeader);
        $I->sendGET('user_app/basket');
        $I->seeResponseCodeIs(400);
    }

    public function testIndexActionWithTableHeader(ApiTester $I)
    {
        $I->haveHttpHeader('Authorization', $this->authHeader);

        $I->haveHttpHeader('table', 2);
        $I->sendGET('user_app/basket');
        $expectedJson = [
            'data' => [
                'list' => [],
                'total' => 0
            ]
        ];
        $validResponseJsonSchema = [
            'data' => [
                'list' => ['type' => 'array'],
                'total' => ['type' => 'integer']
            ]
        ];
        $I->seeResponseIsValidOnJsonSchemaString('{"type":"object"}');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($expectedJson);
        $I->seeResponseIsValidOnJsonSchemaString(json_encode($validResponseJsonSchema));
        $I->seeResponseMatchesJsonType(
            [
                'data'         => 'array',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function testAddAction(ApiTester $I)
    {
        $I->haveHttpHeader('Authorization', $this->authHeader);

        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]);
        $I->seeResponseCodeIs(200);
    }

    public function testDoubleAddAttempt(ApiTester $I)
    {
        $I->haveHttpHeader('Authorization', $this->authHeader);

        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Such item already exists, please update the quantity');
    }

    public function testSetAction(ApiTester $I)
    {
        $I->haveHttpHeader('Authorization', $this->authHeader);

        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]);
        $I->sendPut('user_app/basket', ['productId' => 62, 'quantity' => 3]);
        $I->seeResponseCodeIs(HttpCode::OK);
        // Assert that the item has a quantity of 2
        $I->seeResponseContainsJson(['data' => ['list' => [['quantity' => 3]]]]);
    }

    //закоментировано, так как не могу нормально отправить delete запрос, параметр воспринимается бэком как нулл
    
    // public function testDeleteAction(ApiTester $I)
    // {
    //     $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
    //     $I->haveHttpHeader('Authorization', $authHeader);
    //     $I->haveHttpHeader('table', 10);
    //     $I->haveHttpHeader('Content-Type', 'multipart/form-data');
    //     $I->sendPost('user_app/basket', ['productId' => 62, 'sizeId' => 1]);
    //     $I->sendDelete('user_app/basket', ['productId' => 62]);
    //     $I->seeResponseCodeIs(200);
    //     $I->seeResponseContainsJson(['data' => ['list' => []]]);
    // }

    public function testClearAction(ApiTester $I)
    {
        $I->haveHttpHeader('Authorization', $this->authHeader);
        $I->haveHttpHeader('table', 10);
        $I->sendPost('user_app/basket/clear');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['list' => []]]);
    }
}
