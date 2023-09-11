<?php

namespace app\controllers\api;

use app\models\tables\Basket;
use Yii;
use app\controllers\ApiController;
use app\models\tables\BasketItem;
use app\models\tables\Table;

class OrderableController extends ApiController
{
    protected $basket = null;

    public function getUser()
    {
        return Yii::$app->user->identity;
    }

    protected function getBasketItems()
    {
        $filter = [
            'table_id' => Yii::$app->session->get('table_number'),
            'order_id' => null
        ];
        $result = $this->getBasketItemsByFilter($filter);
        $total = 0;
        if (isset($result['items'])) {
            $total = self::prepareItems($result['items']);
        }
        return [$result, $total];
    }

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

    private function getBasketItemsByFilter($arFilter)
    {
        return Basket::find()
            ->joinWith('items')
            ->joinWith('items.product')
            ->joinWith('items.product.categories')
            ->where($arFilter)->cache(false)->asArray()->one();
    }

    protected function getBasketItemsFromOrder($orderId, $address_id = 0, $new_quantities = [], $is_express = false)
    {
        $result = $this->getBasketItemsByFilter(['order_id' => $orderId], $address_id);
        $total = 0;
        if (isset($result['items'])) {
            foreach ($result['items'] as &$item) {
                $item['bonus'] = $item['price'] = $item['promotion_id'] = $item['tariff_id'] = null;
            }
            unset($item);
        }

        $result['is_express'] = (string)(($is_express) ? 1 : 0);
        return [$result, $total];
    }

    protected function getBasket()
    {
        $obTable = Table::find()->where(['table_number' => \Yii::$app->session->get('table_number')])->one();
        if (!$this->basket) {
            $obBasket = Basket::find()->where(['table_id' => $obTable->id, 'order_id' => null])->one();
            if (!$obBasket) {
                $obBasket = new Basket();
                $obBasket->table_id = $obTable->id;
                $obBasket->created_at = time();
                $obBasket->updated_at = time();
                $obBasket->save();
            }
            $this->basket = $obBasket;
        }
        return $this->basket;
    }
}
