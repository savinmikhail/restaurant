<?php

namespace app\models\tables;

use app\models\Base;

class Basket extends Base
{
    public function rules()
    {
        return [
            [['table_id'], 'required'],
            [['order_id', 'table_id', 'created_at', 'updated_at',], 'integer'],
        ];
    }

    protected function recordTableName()
    {
        return 'baskets';
    }

    public static function tableName()
    {
        return 'baskets';
    }

    protected function prefixName()
    {
        return 'basket';
    }

    public function calcTotalSum()
    {
    }

    public function getItems()
    {
        return $this->hasMany(BasketItem::class, ['basket_id' => 'id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function clear()
    {
        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id])->all();
        foreach ($obBasketItem as $obIt) {
            $obIt->delete();
        }
        $this->save();
    }

    public function addItem($productId, $quantity)
    {
        $arProduct = Products::find()->where(['id' => $productId])->asArray()->one();
        if (!$arProduct['id']) {
            return false;
        }
        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id, 'product_id' => $productId])->one();
        if ($obBasketItem) {
            $obBasketItem->quantity += $quantity;
            $obBasketItem->save();
            return [$obBasketItem->id, ''];
        }
        $obBasketItem = new BasketItem();
        $obBasketItem->load(['product_id' => $productId, 'quantity' => $quantity, 'basket_id' => $this->id], '');
        $obBasketItem->save();
        return [$obBasketItem->id, ''];
    }

    public function deleteItem($id)
    {
        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id, 'id' => $id])->one();
        if ($obBasketItem) {
            $obBasketItem->delete();
        }
        $this->save();
    }

    public function updateItem($id, $quantity)
    {
        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id, 'id' => $id])->one();
        if ($obBasketItem) {
            if ($quantity == 0) {
                $obBasketItem->delete();

                return [0, ''];
            }
            $arProduct = Products::find()->where(['id' => $obBasketItem->product_id])->asArray()->one();
            if ($arProduct['quantity'] >= $quantity) {
                $obBasketItem->quantity = $quantity;
                $obBasketItem->save();

                return [$obBasketItem->id, ''];
            } else {
                return [false, 'Вы не можете заказать больше чем ' . $arProduct['quantity'] . 'шт'];
            }
        }
        $obBasketItem = new BasketItem();
        $obBasketItem->load(['id' => $id, 'quantity' => $quantity, 'basket_id' => $this->id], '');
        $obBasketItem->save();

        return [$obBasketItem->id, ''];
    }
}
