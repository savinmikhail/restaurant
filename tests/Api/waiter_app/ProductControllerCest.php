<?php


namespace Api\waiter_app;

use \ApiTester;
use Codeception\Attribute\Examples;
use Codeception\Example;
use Codeception\Util\HttpCode;

class ProductControllerCest
{
    private string $authHeader;
    public function _before(ApiTester $I)
    {
        $this->authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $this->authHeader);
    }

    #[Examples(
        ['productName' => '', 'page' => 1, 'perPage' => 10, 'responseCode' => HttpCode::OK],
        ['productName' => 'nonExistingProductName', 'page' => null, 'perPage' => null, 'responseCode' => HttpCode::OK],
    )]
    public function testIndexAction(ApiTester $I, Example $example)
    {
        $I->sendGET('waiter_app/products', ['page' => $example['page'], 'perPage' => $example['perPage'], 'productName' => $example['productName'],]);
        $I->seeResponseCodeIs($example['responseCode']);
    }
}
