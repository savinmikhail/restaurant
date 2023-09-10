<?php

namespace app\models\tables;

use app\models\Base;

class Products extends Base
{
    protected $int_vals = [
        'active',
    ];

    public function rules()
    {
        return [
            [['name', 'sort', 'category_id', 'base_price'], 'required'],
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

    public function getProductProperties()
    {
        return $this->hasMany(ProductsPropertiesValues::class, ['product_id' => 'id']);
    }

    public function getProductImages()
    {
        return $this->hasMany(ProductsImages::class, ['product_id' => 'id']);
    }

    public function getTariff()
    {
        return $this->hasMany(TariffTable::class, ['product_id' => 'id']);
    }

    public function getProductsPromotions()
    {
        return $this->hasMany(ProductsPromotions::class, ['product_id' => 'id']);
    }
}
