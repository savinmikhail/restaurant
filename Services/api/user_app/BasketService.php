<?php

namespace app\Services\api\user_app;

use app\Services\BasketItemsHelper;
use app\models\tables\Basket;
use app\models\tables\PaymentType;
use app\models\tables\Table;
use app\Services\api\BaseService;
use Exception;

class BasketService extends BaseService
{
    protected $basket = null;

    /**
     * Retrieves the necessary data for constructing a response.
     *
     * @return array Returns an array containing the HTTP status code and the data for the response.
     */
    public function getDataForResponse(): array
    {
        list($result, $total) = $this->getBasketItems();

        $output = $this->reOrganizeResponseArray($result);

        $data = [
            'data' => [
                'list' => $output,
                'total' => $total,
            ]
        ];
        return array(self::HTTP_OK, $data);
    }

    /**
     * Retrieves the basket items.
     *
     * @return array The basket items.
     */
    protected function getBasketItems(): array
    {
        $obTable = Table::getTable();

        $filter = [
            'table_id' => $obTable->id,
        ];
        $result = $this->getBasketItemsByFilter($filter);
        $total = 0;
        if ($result && isset($result['items'][0]['basket_id'])) {
            $obBasket = Basket::find()->where(['id' => $result['items'][0]['basket_id']])->one();
            $total = $obBasket ? $obBasket->basket_total : 0;
        }
        return [$result, $total];
    }

    /**
     * Retrieves basket items based on the provided filter.
     *
     * @param array $arFilter The filter to apply when retrieving basket items.
     * @return array|null The retrieved basket items, or null if no items are found.
     */
    private function getBasketItemsByFilter(array $arFilter): ?array
    {
        return Basket::find()
            ->joinWith('items')
            ->joinWith('items.product')
            ->joinWith('items.product.categories')
            ->joinWith('items.product.productSizePrices.price')
            ->joinWith('items.product.productSizePrices.size')
            ->where($arFilter)
            ->cache(false)
            ->asArray()
            ->one();
    }

    /**
     * Reorganizes the response array by extracting necessary information from the given result array.
     *
     * @param array $result The input array containing the result data.
     * @return array The reorganized array with extracted information.
     */
    private function reOrganizeResponseArray(array $result): array
    {
        if (empty($result['items'])) {
            return $result;
        }
        $output = [];
        foreach ($result['items'] as $item) {
            $sizeName = '';

            // Find the matching size name
            foreach ($item['product']['productSizePrices'] as $sizePrice) {
                if ($sizePrice['size_id'] === $item['size_id']) {
                    $sizeName = $sizePrice['size']['name'];
                    break;
                }
            }

            $restructuredItem = [
                'productId' => $item['product_id'],
                'image'     => $item['product']['image'],
                'name'      => $item['product']['name'],
                'quantity'  => $item['quantity'],
                'price'     => $item['price'],
                'size'      => $sizeName,
                'sizeId'    => $item['size_id']
            ];

            $output[] = $restructuredItem;
        }

        return $output;
    }

    /**
     * Retrieves the basket associated with the current instance.
     *
     * @throws Exception If unable to define the table.
     * @throws Exception If failed to save the basket.
     * @return Basket The basket associated with the current instance.
     */
    protected function getBasket(): Basket
    {
        $obTable = Table::getTable();
        if (!$obTable) {
            throw new Exception('Unable to define table');
        }
        if (!$this->basket) {
            $obBasket = Basket::find()->where(['table_id' => $obTable->id])->one();
            if (!$obBasket) {
                $obBasket = new Basket();
                $obBasket->table_id = $obTable->id;
                if (!$obBasket->save()) {
                    throw new Exception("Failed to save Basket: " . print_r($obBasket->errors, true));
                }
            }
            $this->basket = $obBasket;
        }
        return $this->basket;
    }

    /**
     * Retrieves an array of payment methods.
     *
     * @return array An array containing the HTTP response code and the payment types.
     */
    public function getPaymentMethods(): array
    {
        $paymentTypes = PaymentType::find()->select('name')->column();
        return array(self::HTTP_OK, $paymentTypes);
    }

    /**
     * Add an item to the basket.
     *
     * @param int $productId The ID of the product to be added.
     * @param int $quantity The quantity of the product to be added.
     * @param int $sizeId The ID of the size of the product to be added.
     * @throws Exception If an error occurs while adding the item to the basket.
     * @return array The HTTP status code and any error message if an error occurred, otherwise the data for the response.
     */
    public function addItem(int $productId, int $quantity, int $sizeId): array
    {
        try {
            $this->getBasket()->addItem($productId, $quantity, $sizeId);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }

        list($result) = $this->getBasketItems();
        BasketItemsHelper::prepareItems($result['items']);

        return $this->getDataForResponse();
    }

    /**
     * Updates the quantity of a product in the basket.
     *
     * @param int $productId The ID of the product.
     * @param int $quantity The new quantity of the product.
     * @throws Exception If an error occurs while updating the item in the basket.
     * @return array The data to be returned in the response.
     */
    public function setQuantity(int $productId, int $quantity): array
    {
        try {
            $this->getBasket()->updateItem($productId, $quantity);
            list($result) = $this->getBasketItems();
            BasketItemsHelper::prepareItems($result['items']);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }

        return $this->getDataForResponse();
    }

    /**
     * A description of the entire PHP function.
     *
     * @param int $productId description of the parameter
     * @throws Exception description of the exception
     * @return array
     */
    public function deleteItem(int $productId): array
    {
        try {
            $this->getBasket()->deleteItem($productId);
        } catch (Exception $e) {
            return array(400, $e->getMessage());
        }

        list($result) = $this->getBasketItems();

        if (!empty($result['items'])) {
            BasketItemsHelper::prepareItems($result['items']);
        }

        return $this->getDataForResponse();
    }

    /**
     * Clears the basket by calling the clear method of the basket object.
     *
     * @throws Exception if an error occurs while clearing the basket
     * @return array an array containing the HTTP_BAD_REQUEST status code and the error message if an exception is thrown, otherwise the data for the response
     */
    public function clearBasket(): array
    {
        try {
            $this->getBasket()->clear();
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }

        return $this->getDataForResponse();
    }
}
