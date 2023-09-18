<?php

namespace app\common;

use app\models\tables\Basket;
use app\models\tables\BasketItem;
use app\models\tables\Modifier;
use app\models\tables\Price;
use app\models\tables\Products;
use app\models\tables\SizePrice;
use Yii;

class Util
{
    public static function prepareItems(array &$items)
    {
        if (!$items) {
            throw new \Exception("Empty basket");
        }

        foreach ($items as &$item) {
            $obBasketItem = BasketItem::find()->where(['id' => $item['id']])->one();

            self::calcMainPrice($item);

            self::applyModifiers($item); //calculate the sum of the modifiers

            $obBasketItem->price = $item['price'];
            $obBasketItem->save();
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

            //если не существует (size_id = null)
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
    
    private static function applyModifiers(array &$item)
    {
        if($item['modifier_id']){
            $obModifier = Modifier::find()->where(['id' => $item['modifier_id']])->one();
            $obProduct = Products::find()->where(['external_id' => $obModifier->external_id])->one();
            $obSizePrice = SizePrice::find()->where(['size_id' => $item['size_id']])->andWhere(['product_id' => $obProduct->id])->one();
            $obPrice = Price::find()->where(['size_price_id' => $obSizePrice->id])->one();
            if ($obPrice->is_included_in_menu) {
                $item['price'] += $obPrice->current_price * $item['modifier_quantity'];
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
