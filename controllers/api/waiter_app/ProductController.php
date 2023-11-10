<?php

namespace app\controllers\api\waiter_app;

use app\controllers\api\ApiController;
use app\models\tables\Products;
use Yii;
use yii\data\Pagination;
use yii\filters\VerbFilter;

class ProductController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Restricting actions by verbs (HTTP methods)
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index'  => ['GET'],
            ],
        ];
        return $behaviors;
    }

    /**
     * @SWG\Get(path="/api/waiter_app/products",
     *     tags={"WaiterApp\Products"},
     *     @SWG\Parameter(
     *         name="productName",
     *         in="query",
     *         description="Name of the product to filter by",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         type="integer"
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
        $page = Yii::$app->request->get('page', 1);
        $perPage = Yii::$app->request->get('perPage', 10);

        // Prepare the query
        $query = Products::find()
            ->select(['products.id', 'products.name'])
            ->joinWith([
                'sizes' => function ($query) {
                    $query->select(['sizes.id', 'sizes.name']); 
                }
            ])
            ->andFilterWhere(['like', 'products.name', $productNameFilter]);  //when $productNameFilter is an empty string, the filter will be ignored, and the query will return all products

        // Set up pagination
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $perPage]);
        $pages->setPage($page - 1); // Adjust page number (0 indexed)

        $productsData = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        $this->sendResponse(200, [
            'data' => $productsData,
            'pagination' => [
                'totalCount' => $pages->totalCount,
                'page' => $pages->page + 1, // Return 1 indexed page number
                'perPage' => $pages->pageSize,
            ],
        ]);
    }
}
