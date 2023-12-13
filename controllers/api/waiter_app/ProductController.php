<?php

namespace app\controllers\api\waiter_app;

use app\controllers\api\ApiController;
use app\Services\api\waiter_app\ProductService;
use Yii;
use yii\filters\VerbFilter;

class ProductController extends ApiController
{
    public function __construct($id, $module,private ProductService $productService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

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
        $page = Yii::$app->request->get('page');
        $perPage = Yii::$app->request->get('perPage');
        // If pagination params are not set
        if (is_null($page) || is_null($perPage)) {
            list($code, $data) = $this->productService->getListData($productNameFilter);
            $this->sendResponse($code, $data);
        }
        list($code, $data) = $this->productService->getListDataPaginated($page, $perPage, $productNameFilter);
        $this->sendResponse($code, $data);
    }
}
