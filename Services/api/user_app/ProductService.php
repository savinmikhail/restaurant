<?php

namespace app\Services\api\user_app;

use app\models\tables\Products;

class ProductService
{
    public function getListData(string $productNameFilter): array
    {
        $productsQuery = Products::find()
            ->joinWith('categories')
            ->joinWith('productSizePrices.price')
            ->joinWith('productSizePrices.size')
            ->joinWith('tags')
            ->andFilterWhere(['like', 'products.name', $productNameFilter])  //when $productNameFilter is an empty string, the filter will be ignored, and the query will return all products
            ->asArray();

        $productsData = $productsQuery->all();

        $result = $this->restructurizeProductsData($productsData);
        return [200, $result];
    }

    private function restructurizeProductsData(array $productsData): array
    {
        $result = [];

        foreach ($productsData as $product) {
            $productData = [
                'categoryId' =>  $product['category_id'],
                'categoryName' => $product['categories'] ? $product['categories']['name'] : '',
                'productId' => $product['id'],
                'image' => $product['image'],
                'productName' => $product['name'],
                'description' => $product['description'],
                'isActive' => $product['balance'] > 0 ? 1 : 0, //TODO: возможно имеет смысл поставить дефолтное значение в бд на null
                'tags' => [],
                'sizePrices' => [],
            ];

            foreach ($product['productSizePrices'] as $sizePrice) {
                $productData['sizePrices'][] = [
                    'sizeName' => $sizePrice['size'] ? $sizePrice['size']['name'] : '',
                    'sizeId' => $sizePrice['size'] ? $sizePrice['size']['id'] : '',
                    'price' => $sizePrice['price']['current_price'],
                ];
            }

            foreach ($product['tags'] as $tag) {
                $productData['tags'][] = $tag['name'];
            }

            $result[] = $productData;
        }
        return $result;
    }
}
