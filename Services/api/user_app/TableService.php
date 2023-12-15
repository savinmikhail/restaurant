<?php

namespace app\Services\api\user_app;

use app\models\tables\Basket;
use app\models\tables\Order;
use app\models\tables\Table;
use app\Services\api\BaseService;

class TableService extends BaseService
{
    public function __construct(private IikoTransportService $iikoTransportService)
    {
    }
    /**
     * Call the waiter and return the response.
     *
     * @return array The response from calling the waiter.
     */
    public function callWaiter(): array
    {
        list($code, $data) = $this->iikoTransportService->callWaiter();
        return array($code, $data);
    }

    /**
     * Closes the current table and clears the basket.
     *
     * @throws \Exception if the table number is not provided in the request.
     */
    public function closeTable(): array
    {
        $tableNumber = \Yii::$app->request->post('tableNumber');
        if (!$tableNumber) {
            return array(self::HTTP_BAD_REQUEST, 'Table number is not provided');
        }
        $table = Table::find()->where(['table_number' => $tableNumber])->one();
        if (!$table) {
            return array(self::HTTP_NOT_FOUND, 'Table not found');
        }
        $basket = Basket::find()->where(['table_id' => $table->id])->one();
        if (!$basket) {
            return array(self::HTTP_NOT_FOUND, 'Basket not found');
        }
        $basket->clear();
        Order::deleteAll(['basket_id' => $basket->id]);
        return array(self::HTTP_OK, 'Table was closed');
    }
}
