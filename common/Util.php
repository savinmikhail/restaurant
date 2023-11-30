<?php

namespace app\common;

use app\models\tables\Basket;
use app\models\tables\BasketItem;

class Util
{
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
    
    public static function calcTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
