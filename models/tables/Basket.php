<?php

namespace app\models\tables;

use app\models\Base;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Basket extends Base
{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'), // Or 'value' => time() for Unix timestamp
            ],
        ];
    }

    public function rules()
    {
        return [
            [['table_id'], 'required'],
            [['table_id', 'basket_total'], 'integer'],
            [['created_at', 'updated_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
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
        BasketItem::deleteAll(['basket_id' => $this->id]);
    }

    public function addItem(int $productId, int $quantity, int $sizeId): BasketItem
    {
        $Product = Products::find()
            ->where(['id' => $productId])
            ->one();

        if (!$Product) {
            throw new \Exception("Failed to find Product with id $productId");
        }
        if ($quantity > (int)$Product->balance) {
            throw new \Exception("Not enough products in stock, available only: " . $Product->balance); //TODO: имплементировать крон на постоянное обновление
        }
        $obBasketItem = BasketItem::find()
            ->where(['basket_id' => $this->id, 'product_id' => $productId, 'size_id' => $sizeId]) //если есть такой продукт такого же размера, то увеличим количество
            ->one();

        if ($obBasketItem) {
            $obBasketItem->quantity += $quantity;
        } else {
            $obBasketItem = new BasketItem();
            $obBasketItem->load(['product_id' => $productId, 'quantity' => $quantity, 'basket_id' => $this->id,], '');
        }
        $obBasketItem->size_id = $sizeId;

        if (!$obBasketItem->save()) {
            throw new \Exception("Failed to save Basket Item: " . print_r($obBasketItem->errors, true));
        }
        $Product->balance -= $quantity;
        if (!$Product->save()) {
            throw new \Exception("Failed to save Product: " . print_r($Product->errors, true)); //TODO: надо чтоб запрос кроном из айки не перезаписал это значение, пока не уйдет в заказ
        }
        return $obBasketItem;
    }

    public function deleteItem(int $productId)
    {
        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id, 'product_id' => $productId])->one();
        if ($obBasketItem) {
            return $obBasketItem->delete(); //false or integer
        }
        throw new \Exception("Failed to find Basket Item with product id $productId, basket id $this->id");
    }

    public function updateItem(int $productId, int $quantity): BasketItem
    {
        $Product = Products::find()
            ->where(['id' => $productId])
            ->one();

        if (!$Product) {
            throw new \Exception("Failed to find Product with id $productId");
        }

        if ($quantity > (int)$Product->balance) {
            throw new \Exception("Not enough products in stock, available only: " . $Product->balance); //TODO: имплементировать крон на постоянное обновление
        }

        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id, 'product_id' => $productId])->one();
        if (!$obBasketItem) {
            throw new \Exception("Failed to find Basket Item with product id $productId, basket id $this->id");
        }

        if ($obBasketItem->quantity !== $quantity) {
            $obBasketItem->quantity = $quantity;
            if (!$obBasketItem->save()) {
                throw new \Exception("Failed to save Basket Item: " . print_r($obBasketItem->errors, true));
            }
        }

        $Product->balance -= $quantity;
        if (!$Product->save()) {
            throw new \Exception("Failed to save Product: " . print_r($Product->errors, true)); //TODO: надо чтоб запрос кроном из айки не перезаписал это значение, пока не уйдет в заказ
        }
        
        return $obBasketItem;
    }

    public function getBasketItems()
    {
        return $this->find()
            ->joinWith('items')
            ->joinWith('items.product')
            ->joinWith('items.product.categories')
            ->joinWith('items.product.productSizePrices.price')
            ->joinWith('items.product.productSizePrices.size')
            ->cache(false)
            ->asArray()
            ->one();
    }
}
