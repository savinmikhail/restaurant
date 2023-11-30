<?php

namespace app\Services\api\waiter_app;

use app\common\Util;
use app\models\tables\Basket;
use app\models\tables\BasketItem;
use app\models\tables\Order;
use app\models\tables\Products;
use yii\data\Pagination;

class ProductService
{
    public function getListDataPaginated(int $page, int $perPage, string $productNameFilter): array
    {
        // Prepare the query
        $query = Products::find()
            ->select(['products.id', 'products.name'])
            ->joinWith([
                'sizes' => function ($query) {
                    $query->select(['sizes.id', 'sizes.name']);
                }
            ])
            //when $productNameFilter is an empty string, the filter will be ignored, and the query will return all products
            ->andFilterWhere(['like', 'products.name', $productNameFilter]);

        // Set up pagination
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $perPage]);
        $pages->setPage($page - 1); // Adjust page number (0 indexed)

        $productsData = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        $output = [
            'data' => $productsData,
            'pagination' => [
                'totalCount' => $pages->totalCount,
                'page' => $pages->page + 1,
                // Return 1 indexed page number
                'perPage' => $pages->pageSize,
            ],
        ];
        return array(200, $output);
    }

    public function getListData(string $productNameFilter): array
    {
        // Prepare the query
        $query = Products::find()
            ->select(['products.id', 'products.name'])
            ->joinWith([
                'sizes' => function ($query) {
                    $query->select(['sizes.id', 'sizes.name']);
                }
            ])
            //when $productNameFilter is an empty string, the filter will be ignored, and the query will return all products
            ->andFilterWhere(['like', 'products.name', $productNameFilter]);

        $productsData = $query->asArray()->all();

        $output = [
            'data' => $productsData,
        ];
        return array(200, $output);
    }
}
