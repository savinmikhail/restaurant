<?php


namespace Unit\user_app;

use \UnitTester;
use app\Services\api\user_app\BasketService;
use app\Services\BasketItemsHelper;
use app\models\tables\Basket;
use yii\db\Connection;
use yii\db\Transaction;
use yii\web\Request;

class BasketTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    // protected function _before()
    // {
    // }

    // public function testSetQuantity()
    // {
    //     $mock = $this->getMockBuilder('app\Services\api\user_app\BasketService')
    //         ->onlyMethods(['setQuantity'])
    //         ->getMock();

    //     $result = $mock->expects($this->once())
    //         ->method('setQuantity')
    //         ->with([1, 2])
    //         ->willReturn([400]);

    //     $this->assertIsArray($result->getReturnValue());
    //     $this->assertEquals(400, $result->getReturnValue()[0]);
    // }
}
