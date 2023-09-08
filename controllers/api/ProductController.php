<?php

namespace app\controllers\api;

use app\controllers\api\OrderableController;
use app\models\Products;

class ProductController extends OrderableController
{
    /**
     * @SWG\Get(path="/api/catalog/products",
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

        return $this->asJson([
            'products' => $Products,
        ]);
    }
    
}