<?php

namespace app\controllers\api\user_app;

use app\controllers\api\user_app\OrderableController;
use app\models\tables\Order;

class TableController extends OrderableController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Restricting actions by verbs (HTTP methods)
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index'  => ['GET'],
                // 'view'   => ['GET'],
                // 'create' => ['POST'],
                // 'update' => ['PUT', 'PATCH'],
                // 'delete' => ['DELETE'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @SWG\Post(path="/api/user_app/table/close",
     *     tags={"UserApp\Table"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Empty response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionClose()
    {
        $this->getBasket()->clear();
        Order::deleteAll(['basket_id' => $this->getBasket()->id]);
        return $this->sendResponse(200, 'Table was closed');
    }
}
