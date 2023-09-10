<?php

namespace app\models\tables;

use app\models\Base;

class Basket extends Base
{
    public function rules()
    {
        return [
            [['fuser_id'], 'required'],
            [['order_id', 'fuser_id', 'created', 'updated', 'is_express'], 'integer'],
            //[['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
            //[['line_id'], 'exist', 'skipOnError' => true, 'targetClass' => Line::class, 'targetAttribute' => ['line_id' => 'id']],
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

    public function setBasketExpressStatus($flag = false)
    {
        $this->is_express = $flag;
        $this->save();
        if ($flag) {
            $res = $this->getItems()->all();
            foreach ($res as $item) {
                $product = $item->getProduct()->asArray()->one();
                if (!$product['express_delivery_enabled']) {
                    $item->delete();
                }
            }
            $res = $this->getItems()->all();
            if ($res) {
                $this->is_express = false;
                $this->save();
            }
        }
    }

    public function beforeSave($insert)
    {

        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id])->all();
        if (!$obBasketItem) $this->is_express = false;
        return parent::beforeSave($insert);
    }

    public function clear()
    {
        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id])->all();
        foreach ($obBasketItem as $obIt) {
            $obIt->delete();
        }
        $this->coupon = '';
        $this->is_express = 0;
        $this->save();
    }


    private function checkContains($productId)
    {
        $arSourceProduct = Products::find()->where(['id' => $productId])->asArray()->one();
        $arBasketItem = BasketItem::find()->where(['basket_id' => $this->id])->asArray()->all();
        $ids = [];
        foreach ($arBasketItem as $item) {
            $ids[] = $item['product_id'];
        }
        if (!$ids) {
            return true;
        }
        $arTargetProducts = Products::find()->where(['in', 'id', $ids])->asArray()->all();
        foreach ($arTargetProducts as $product) {
            if ($product['delivery_separately'] != $arSourceProduct['delivery_separately']) {
                if ($arSourceProduct['is_water']) {
                    throw new \Exception('уже есть ' . $product['name'], 1);
                } else {
                    throw new \Exception('уже есть ' . $product['name'], 2);
                }
            }
        }

        return true;
    }

    public function addItem($productId, $quantity)
    {
        if (!$this->checkContains($productId)) {
            return false;
        }
        $arProduct = Products::find()->where(['id' => $productId])->asArray()->one();
        if (!$arProduct['id']) {
            return false;
        }
        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id, 'product_id' => $productId])->one();
        if ($obBasketItem) {
            if ($arProduct['quantity'] >= $obBasketItem->quantity + $quantity) {
                $obBasketItem->quantity += $quantity;
                $obBasketItem->save();

                return [$obBasketItem->id, ''];
            } else {
                return [false, 'Вы не можете заказать больше чем ' . $arProduct['quantity'] . 'шт'];
            }
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
