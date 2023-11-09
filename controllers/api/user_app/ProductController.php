<?php

namespace app\controllers\api\user_app;

use app\controllers\api\user_app\OrderableController;
use app\models\tables\Products;
use Yii;

class ProductController extends OrderableController
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
     * @SWG\Get(path="/api/user_app/products",
     *     tags={"UserApp\Products"},
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
            ->joinWith('tags')
            ->andFilterWhere(['like', 'products.name', $productNameFilter])  //when $productNameFilter is an empty string, the filter will be ignored, and the query will return all products
            ->asArray();

        $productsData = $productsQuery->all();

        $result = [];

        foreach ($productsData as $product) {

            $productData = [
                'categoryId' =>  $product['category_id'],
                'categoryName' => $product['categories']['name'],
                'productId' => $product['id'],
                'image' => $product['image'],
                'productName' => $product['name'],
                'description' => $product['description'],
                'isActive' => rand(0, 1), //TODO: pull data from iiko
                'tags' => [],
                'sizePrices' => [],
            ];

            foreach ($product['productSizePrices'] as $sizePrice) {
                $productData['sizePrices'][] = [
                    'sizeName' => $sizePrice['size']['name'],
                    'sizeId' => $sizePrice['size']['id'],
                    'price' => $sizePrice['price']['current_price'],
                ];
            }

            foreach ($product['tags'] as $tag) {
                $productData['tags'][] = $tag['name'];
            }
            
            $result[] = $productData;
        }

        return $this->asJson([
            'data' => $result,
        ]);
    }
}
