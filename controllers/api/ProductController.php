<?php

namespace app\controllers\api;

use app\controllers\api\OrderableController;
use app\models\tables\Products;
use Yii;

class ProductController extends OrderableController
{
    /**
     * @SWG\Get(path="/api/products",
     *     tags={"Catalog"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        $Products = Products::find()->all();
        $session = Yii::$app->session;

        return $this->asJson([
            'products' => $Products,
            'table_number3' => $session->get('table_number')
        ]);
    }
    
}