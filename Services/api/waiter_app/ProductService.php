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
    /**
     * Retrieves paginated list data based on the given page, number of items per page, and product name filter.
     *
     * @param int $page The page number to retrieve.
     * @param int $perPage The number of items per page.
     * @param string $productNameFilter The product name filter.
     *        When an empty string is provided, the filter will be ignored and all products will be returned.
     * @return array The paginated list data.
     */
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

    /**
     * Retrieves a list of data based on a product name filter.
     *
     * @param string $productNameFilter The product name filter to apply.
     * @return array The list of data matching the filter.
     */
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
