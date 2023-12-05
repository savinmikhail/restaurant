<?php

namespace app\controllers\api\user_app;

use app\controllers\api\user_app\OrderableController;
use app\Services\api\user_app\ProductService;
use Yii;

class ProductController extends OrderableController
{
    private $productService;

    public function __construct($id, $module, ProductService $productService, $config = [])
    {
        $this->productService = $productService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index'  => ['GET'],
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

        list($code, $data) = $this->productService->getListData($productNameFilter);

        $this->sendResponse($code, $data);
    }
}
