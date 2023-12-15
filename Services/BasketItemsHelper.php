<?php

namespace app\Services;

use app\models\tables\Basket;
use app\models\tables\BasketItem;
use Exception;

class BasketItemsHelper
{
    /**
     * Prepares the items in the given array.
     *
     * @param array &$items The array of items to be prepared.
     * @throws Exception If the basket is empty.
     * @return int The total calculated after preparing the items.
     */
    public static function prepareItems(array &$items)
    {
        if (!$items) {
            throw new Exception("Empty basket");
        }

        foreach ($items as &$item) {
            self::calcMainPrice($item);

            BasketItem::updateAll(['price' => $item['price']], ['id' => $item['id']]);
        }
        unset($item);

        $total = self::calcTotal($items);

        $obBasket = Basket::find()->where(['id' => $items[0]['basket_id']])->one();
        $obBasket->basket_total = $total;
        $obBasket->save();

        return $total;
    }

    /**
     * Calculates the main price for an item.
     *
     * @param array &$item The item to calculate the main price for.
     * @return void
     */
    private static function calcMainPrice(array &$item)
    {
        $sizePrices = $item['product']['productSizePrices'];

        $item['price'] = self::getPriceForSize($item['size_id'], $sizePrices);

        if ($item['price'] === 0) {
            // If size_id is null, apply the first available price
            $item['price'] = self::getFirstAvailablePrice($sizePrices);
        }
    }

    /**
     * Get the price for a specific size.
     *
     * @param int|null $sizeId The size ID to find the price for.
     * @param array $sizePrices The array of size prices.
     * @return int
     */
    private static function getPriceForSize(?int $sizeId, array $sizePrices): int
    {
        foreach ($sizePrices as $sizePrice) {
            if ($sizePrice['size_id'] === $sizeId && self::isPriceActive($sizePrice['price'])) {
                return $sizePrice['price']['current_price'];
            }
        }

        return 0;
    }

    /**
     * Get the first available price.
     *
     * @param array $sizePrices The array of size prices.
     * @return int
     */
    private static function getFirstAvailablePrice(array $sizePrices): int
    {
        foreach ($sizePrices as $sizePrice) {
            if (self::isPriceActive($sizePrice['price'])) {
                return $sizePrice['price']['current_price'];
            }
        }

        return 0;
    }

    /**
     * Check if a price is active.
     *
     * @param array $price The price array.
     * @return bool
     */
    private static function isPriceActive(array $price): bool
    {
        return $price['is_included_in_menu'] === 1 && $price['current_price'];
    }


    /**
     * Calculates the total value of items in an array.
     *
     * @param array $items An array of items with 'price' and 'quantity' keys.
     * @return int The calculated total value.
     */
    private static function calcTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
