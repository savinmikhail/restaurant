<?php

namespace app\models\tables;

use yii\db\ActiveRecord;

class BasketItem extends ActiveRecord
{
    public function rules()
    {
        return [
            [['basket_id', 'product_id'], 'required'],
            [['basket_id', 'product_id', 'quantity', 'tariff_id', 'promotion_id'], 'integer'],
            [['price', 'bonus'], 'double'],
        ];
    }

    public static function tableName()
    {
        return 'basket_items';
    }

    public function getProduct()
    {
        return $this->hasOne(Products::class, ['id' => 'product_id']);
    }

    public function getTariff()
    {
        return $this->hasOne(Tariffs::class, ['id' => 'tariff_id']);
    }

    protected function prefixName()
    {
        return 'basket_items';
    }

    public function getBasket()
    {
        return $this->hasOne(Basket::class, ['id' => 'basket_id']);
    }

}
