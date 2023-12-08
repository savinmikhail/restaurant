<?php

namespace app\Services\api\user_app;

use app\models\tables\Products;

class ProductService
{
    /**
     * Retrieves a list of products based on a filter.
     *
     * @param string $productNameFilter The filter to apply to the product names.
     * @return array The response containing the HTTP status code and the list of products.
     */
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

    /**
     * Restructurize the given products data into a new format.
     *
     * @param array $productsData The array of products data to be restructurized.
     * @return array The restructurized products data in the new format.
     */
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
                // возможно имеет смысл поставить дефолтное значение в бд на null
                // если значение отлично от нуля - прилетело из айки в стоплисте, не показываем на сайте
                'isActive' => (int) $product['balance'] === 0 ? 1 : 0, 
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
