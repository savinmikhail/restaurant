<?php

namespace app\models\tables;

use app\models\Base;
use app\models\tables\SizePrice;
use app\models\tables\Modifier;
use app\models\tables\GroupModifier;
use app\models\tables\ProductsPropertiesValues;
use app\models\tables\ProductsImages;
use app\models\tables\Categories;

class Products extends Base
{
    protected $int_vals = [
        'active',
    ];

    public function rules()
    {
        return [
            [['name', 'sort', ], 'required'],
            [['base_price', ], 'double'],
            [['sort', 'active', 'category_id'], 'integer'],
            [[ 'active',  ], 'boolean'],
            [['description', 'name', 'external_id'], 'string'],
            [['image'], 'string', 'skipOnEmpty' => true],
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

    public function getProductGroupModifiers()
    {
        return $this->hasMany(GroupModifier::class, ['product_id' => 'id']);
    }

    public function getProductModifiers()
    {
        return $this->hasMany(Modifier::class, ['product_id' => 'id']);
    }

    public function getProductSizePrices()
    {
        return $this->hasMany(SizePrice::class, ['id' => 'product_id']);
    }
}
