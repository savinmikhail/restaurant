<?php

namespace app\controllers\api;

use app\models\Fuser;
use app\models\tables\Basket;
use Yii;
use app\controllers\ApiController;

class OrderableController extends ApiController
{
    protected $basket = null;

    public function getUser()
    {
        return Yii::$app->user->identity;
    }

    protected function getBasketItems($address_id = null)
    {
        $result = $this->getBasketItemsByFilter(['fuser_id' => Fuser::getUserId(), 'order_id' => null],$address_id);
        $total = 0;
        if (isset($result['items'])) {

        }

        return [$result, $total];
    }

    private function getBasketItemsByFilter($arFilter,$address_id=0)
    {
        return Basket::find()
        ->joinWith('items')
        ->joinWith('items.product')
        ->joinWith('items.product.categories')
        ->joinWith(['items.product.tariff as tariff' => function ($query) use ($address_id) {
            //if ($address_id) {
            $query->select('tariff.tariff_id, tariff.price, tariff.sort, tariff.one_piece, tariff.product_id, tariff.limit');
            $query->andOnCondition(['tariff.year' => date('Y'), 'tariff.month' => date('m'), 'tariff.address_id' => intval($address_id)]);
            //}
        }])
        ->where($arFilter)->cache(false)->asArray()->one();
    }

    protected function getBasketItemsFromOrder($orderId,$address_id=0,$new_quantities=[],$is_express = false)
    {
        $result = $this->getBasketItemsByFilter(['order_id' => $orderId],$address_id);
        $total = 0;
        if (isset($result['items'])) {
            foreach ($result['items'] as &$item) {
                $item['bonus'] = $item['price'] = $item['promotion_id'] = $item['tariff_id'] = null;

            }
            unset($item);
        }
    
        $result['is_express'] = (string)(($is_express)?1:0);
        return [$result, $total];
    }

    protected function getBasket()
    {
        if (!$this->basket) {
            $obBasket = Basket::find()->where(['fuser_id' => Fuser::getUserId(), 'order_id' => null])->one();
            if (!$obBasket) {
                $obBasket = new Basket();
                $obBasket->fuser_id = Fuser::getUserId();
                $obBasket->created = time();
                $obBasket->updated = time();
                $obBasket->save();
            }
            $this->basket = $obBasket;
        }

        return $this->basket;
    }
}
