<?php

namespace app\models\tables;

use yii\db\ActiveRecord;

class BasketItem extends ActiveRecord
{
    public function rules()
    {
        return [
            [['basket_id', 'product_id'], 'required'],
            [['basket_id', 'product_id', 'quantity', 'modifier_id', 'modifier_quantity', 'size_id'], 'integer'],
            [['price'], 'double'],
            [['modifier_quantity'], 'validateModifierQuantity','skipOnEmpty' => true, 'skipOnError' => false],
            
        ];
    }
    public function validateModifierQuantity($attribute_name, $modifier_quantity = null)
    {
        $obModifier = Modifier::find()->where(['id' => $this->$attribute_name])->one();
        if ($this->modifier_quantity > $obModifier->max_amount) {
            $this->addError($attribute_name, 'Превышено маскимально допустимое количество модификатора');
            return false;
        }
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
