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
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
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

    /**
     * Clears the basket by deleting all the basket items that are not already part of an order.
     *
     * @throws \yii\db\Exception if there is an error while deleting the basket items.
     */
    public function clear()
    {
        //не удаляю товары, если они уже в составе заказа
        BasketItem::deleteAll(['basket_id' => $this->id, 'order_id' => null]);
    }

    /**
     * Adds an item to the basket.
     *
     * @param int $productId The ID of the product to add.
     * @param int $quantity The quantity of the product to add.
     * @param int $sizeId The ID of the size of the product.
     * @throws \Exception If the product is not found or there are not enough products in stock.
     * @return BasketItem The added basket item.
     */
    public function addItem(int $productId, int $quantity, int $sizeId): BasketItem
    {
        $Product = Products::find()
            ->where(['products.id' => $productId])
            ->joinWith('productSizePrices')
            ->one();
        if (!$Product) {
            throw new \Exception("Failed to find Product with id $productId");
        }
        $arSizeIds = SizePrice::find()->select('size_id')->where(['product_id' => $productId])->column();

        if (!in_array($sizeId, $arSizeIds)) {
            throw new \Exception("There's no such size for this product");
        }
        //логика изменилась - продукты в стоплисте просто не показываем покупателю
        // if ($quantity > (int)$Product->balance) {
        //     throw new \Exception("Not enough products in stock, available only: " . $Product->balance); //TODO: имплементировать крон на постоянное обновление
        // }
        $obBasketItem = BasketItem::find()
            ->where(['basket_id' => $this->id, 'product_id' => $productId, 'size_id' => $sizeId]) //если есть такой продукт такого же размера, то увеличим количество
            ->one();

        if ($obBasketItem) {
            throw new \Exception("Such item already exists, please update the quantity");
        }

        $obBasketItem = new BasketItem();
        $obBasketItem->load(['product_id' => $productId, 'quantity' => $quantity, 'basket_id' => $this->id, 'size_id' => $sizeId], '');

        if (!$obBasketItem->save()) {
            throw new \Exception("Failed to save Basket Item: " . print_r($obBasketItem->errors, true));
        }
        // $Product->balance -= $quantity;
        // if (!$Product->save()) {
        //     throw new \Exception("Failed to save Product: " . print_r($Product->errors, true)); //TODO: надо чтоб запрос кроном из айки не перезаписал это значение, пока не уйдет в заказ
        // }
        return $obBasketItem;
    }

    public function deleteItem(int $productId): bool
    {
        $obBasketItem = BasketItem::find()->where(['basket_id' => $this->id, 'product_id' => $productId])->one();
        if (!$obBasketItem) {
            throw new \Exception("Failed to find Basket Item with product id $productId, basket id $this->id");
        }
        return (bool) $obBasketItem->delete(); //false or integer
    }

    /**
     * Updates the quantity of a basket item.
     *
     * @param int $productId The ID of the product to update.
     * @param int $quantity The new quantity of the product.
     * @throws \Exception If the product is not found, or if there are not enough products in stock.
     * @throws \Exception If the basket item is not found, or if there is an error saving the basket item or the product.
     * @return BasketItem The updated basket item.
     */
    public function updateItem(int $productId, int $quantity): BasketItem
    {
        if($quantity < 0) {
            throw new \Exception("Invalid quantity");
        }

        $Product = Products::find()
            ->where(['id' => $productId])
            ->one();

        if (!$Product) {
            throw new \Exception("Failed to find Product with id $productId");
        }
        //логика изменилась - продукты в стоплисте просто не показываем покупателю

        // if ($quantity > (int)$Product->balance) {
        //     throw new \Exception("Not enough products in stock, available only: " . $Product->balance); //TODO: имплементировать крон на постоянное обновление
        // }

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

        // $Product->balance -= $quantity;
        // if (!$Product->save()) {
        //     throw new \Exception("Failed to save Product: " . print_r($Product->errors, true)); //TODO: надо чтоб запрос кроном из айки не перезаписал это значение, пока не уйдет в заказ
        // }

        return $obBasketItem;
    }

    /**
     * Retrieves the basket items.
     *
     * @return array|null The basket items or null if no items found.
     */
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
