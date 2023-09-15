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
        $obProducts = Products::find()
            ->with(['productPropertiesValues.property', 'productSizePrices.price', 'productModifiers', 'productGroupModifiers.childModifiers'])
            ->all();

        $session = Yii::$app->session;

        $productsData = [];

        foreach ($obProducts as $product) {
            // Access related data for each product
            $productData = [
                'product' => $product,
                'productPropertiesValues' => $product->productPropertiesValues,
                'productSizePrices' => $product->productSizePrices,
                'productModifiers' => $product->productModifiers,
                'productGroupModifiers' => $product->productGroupModifiers,
            ];

            $productsData[] = $productData;
        }

        return $this->asJson([
            'products' => $productsData,
            'table_number' => $session->get('table_number')
        ]);
    }

    
}