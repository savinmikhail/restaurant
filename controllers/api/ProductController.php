<?php

namespace app\controllers\api;

use app\controllers\api\OrderableController;
use app\models\tables\Products;
use Yii;

class ProductController extends OrderableController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Restricting actions by verbs (HTTP methods)
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
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
     * @SWG\Get(path="/api/products",
     *     tags={"Products"},
     *     @SWG\Parameter(
     *         name="productName",
     *         in="query",
     *         description="Name of the product to filter by",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Products collection response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        $productNameFilter = Yii::$app->request->get('productName', '');

        $productsQuery = Products::find()
            ->joinWith('categories')
            ->joinWith('productSizePrices.price')
            ->joinWith('productSizePrices.size')
            ->andFilterWhere(['like', 'products.name', $productNameFilter])  //when $productNameFilter is an empty string, the filter will be ignored, and the query will return all products
            ->asArray();

        $productsData = $productsQuery->all();

        $result = [];
        $categoriesIndex = [];

        foreach ($productsData as $product) {
            // if($product['is_deleted'] !== 1){
            $categoryId = $product['category_id'];

            // check if this category was not added yet
            if (!isset($categoriesIndex[$categoryId])) {
                $categoriesIndex[$categoryId] = count($result);  // save current index

                // push to the array
                $result[] = [
                    'categoryId' => $categoryId,
                    'categoryName' => $product['categories']['name'],
                    'products' => [],
                ];
            }

            $productData = [
                'productId' => $product['id'],
                'image' => $product['image'],
                'productName' => $product['name'],
                'description' => $product['description'],
                'sizePrices' => [],
            ];

            foreach ($product['productSizePrices'] as $sizePrice) {
                // if ($sizePrice['price']['is_included_in_menu'] === 1) {
                $productData['sizePrices'][] = [
                    'sizeName' => $sizePrice['size']['name'],
                    'sizeId' => $sizePrice['size']['id'],
                    'price' => $sizePrice['price']['current_price'],
                ];
                // }
            }

            $result[$categoriesIndex[$categoryId]]['products'][] = $productData;
            // }

        }

        return $this->asJson([
            'data' => $result,
        ]);
    }
}
