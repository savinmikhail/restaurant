<?php

namespace app\Services;

use app\models\tables\Basket;
use app\models\tables\BasketItem;

class BasketItemsHelper
{
    /**
     * Prepares the items in the given array.
     *
     * @param array &$items The array of items to be prepared.
     * @throws \Exception If the basket is empty.
     * @return float The total calculated after preparing the items.
     */
    public static function prepareItems(array &$items)
    {
        if (!$items) {
            throw new \Exception("Empty basket");
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
        //calculate the main price
        if ($item['product']['productSizePrices']) {
            //если указанная порция существует в  sizePrices

            foreach ($item['product']['productSizePrices'] as $sizePrice) {
                //достаем указанную порцию
                if ($sizePrice['size_id'] === $item['size_id']) {
                    //применяем ее цену, если она активна
                    if ($sizePrice['price']['is_included_in_menu'] === 1 && $sizePrice['price']['current_price']) {
                        $item['price'] = $sizePrice['price']['current_price'];
                        break;
                    }
                }
            }

            //если не существует какого либо размера (size_id = null), применим первую попавшуюся
            if ($item['price'] === 0) {
                foreach ($item['product']['productSizePrices'] as $sizePrice) {
                    //применяем ее цену, если она активна
                    if ($sizePrice['price']['is_included_in_menu'] === 1 && $sizePrice['price']['current_price']) {
                        $item['price'] = $sizePrice['price']['current_price'];
                        break;
                    }
                }
            }
        }
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
