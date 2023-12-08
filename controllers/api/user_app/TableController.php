<?php

namespace app\controllers\api\user_app;

use app\controllers\api\user_app\OrderableController;
use app\models\tables\Basket;
use app\models\tables\Order;
use app\models\tables\Table;

class TableController extends OrderableController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Restricting actions by verbs (HTTP methods)
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'close'   => ['PUT'],
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
        $tableNumber = \Yii::$app->request->post('tableNumber');
        $table = Table::find()->where(['table_number' => $tableNumber])->one();
        $basket = Basket::find()->where(['table_id' => $table->id])->one();
        $basket->clear();
        Order::deleteAll(['basket_id' => $basket->id]);
        return $this->sendResponse(200, 'Table was closed');
    }
}
