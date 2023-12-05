<?php

namespace app\models\tables;

use app\models\Base;
use app\models\tables\SizePrice;
use app\models\tables\ProductsPropertiesValues;
use app\models\tables\ProductsImages;
use app\models\tables\Categories;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Products extends Base
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
            [['name', 'sort',], 'required'],
            [['sort', 'is_deleted', 'category_id', 'balance'], 'integer'],
            [['is_deleted',], 'boolean'],
            [['description', 'name', 'external_id', 'code'], 'string'],
            [['image'], 'string', 'skipOnEmpty' => true],
            [['created_at', 'updated_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    protected function recordTableName()
    {
        return 'products';
    }

    protected function prefixName()
    {
        return 'products';
    }

    public function getCategories()
    {
        return $this->hasOne(Categories::class, ['id' => 'category_id']);
    }

    public function getProductPropertiesValues()
    {
        return $this->hasMany(ProductsPropertiesValues::class, ['product_id' => 'id']);
    }

    public function getProductImages()
    {
        return $this->hasMany(ProductsImages::class, ['product_id' => 'id']);
    }

    public function getProductSizePrices()
    {
        return $this->hasMany(SizePrice::class, ['product_id' => 'id']);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
            ->viaTable('tag_product', ['product_id' => 'id']);
    }

    public function getSizes()
    {
        return $this->hasMany(Size::class, ['id' => 'size_id'])
            ->viaTable('size_prices', ['product_id' => 'id']);
    }
}
