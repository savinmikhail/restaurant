<?php

namespace app\controllers\api\user_app;

use app\controllers\api\ApiController;
use app\models\tables\Basket;
use app\models\tables\Order;
use app\models\tables\Table;

class TableController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Restricting actions by verbs (HTTP methods)
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'close'   => ['PUT'],
                'waiter' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * этот метод для айко транспорта для закрытия стола
     *
     * @SWG\Put(path="/api/user_app/table/close",
     *     tags={"UserApp\Table"},
     *      @SWG\Parameter(
     *          name="tableNumber",
     *          in="formData",
     *          type="integer",
     *          description="номер стола"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Empty response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionClose()
    {

        list($code, $data) = $this->tableService->closeTable();
        return $this->sendResponse($code, $data);
    }

    /**
     * Call a waiter
     *
     * @SWG\Get(path="/api/user_app/order/waiter",
     *     tags={"UserApp\Order"},
     *     @SWG\Parameter(
     *         name="table",
     *         in="header",
     *         type="integer",
     *         description="Set the table number here",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionWaiter()
    {
        list($code, $data) = $this->tableService->callWaiter();
        $this->sendResponse($code, $data);
    }
}
