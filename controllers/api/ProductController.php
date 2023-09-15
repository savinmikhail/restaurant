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
        $productsData = Products::find()
            ->joinWith('productPropertiesValues.property')
            ->joinWith('productSizePrices.price')
            ->joinWith('productModifiers')
            ->joinWith('productGroupModifiers.childModifiers')
            ->asArray()
            ->all();
        $session = Yii::$app->session;

        return $this->asJson([
            'products' => $productsData,
            'table_number' => $session->get('table_number')
        ]);
    }
}
