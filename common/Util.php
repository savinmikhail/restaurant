<?php

namespace app\common;

use app\models\tables\BasketItem;
use Yii;

class Util
{
    public static function prepareItems(&$items)
    {
        if (!$items) return;
        $total = 0;

        foreach ($items as &$item) {
            $obBasketItem = BasketItem::find()->where(['id' => $item['id']])->one();
            if ($item['quantity'] === 0.5) {
                $item['price'] = $item['product']['half_price'];
            } else {
                $item['price'] = $item['product']['base_price'];
            }
            $obBasketItem->price = $item['price'];
            $obBasketItem->save();
        }
        unset($item);

        $total = self::calcTotal($items);
        return $total;
    }

    public static function calcTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total = $total + $item['price'] * $item['quantity'];
        }
        return $total;
    }
}