<?php

namespace app\models\tables;

use yii\db\ActiveRecord;

class BasketItem extends ActiveRecord
{
    public function rules()
    {
        return [
            [['basket_id', 'product_id'], 'required'],
            [['basket_id', 'product_id', 'quantity', 'size_id'], 'integer'],
            [['price'], 'double'],
            
        ];
    }

    public static function tableName()
    {
        return 'basket_items';
    }

    protected function prefixName()
    {
        return 'basket_items';
    }

    public function getProduct()
    {
        return $this->hasOne(Products::class, ['id' => 'product_id']);
    }

    public function getBasket()
    {
        return $this->hasOne(Basket::class, ['id' => 'basket_id']);
    }


    public function getSize()
    {
        return $this->hasOne(Size::class, ['id' => 'size_id']);
    }

}
