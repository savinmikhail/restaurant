<?php

namespace app\models\tables;

use app\models\Base;
use app\models\tables\SizePrice;
use app\models\tables\ProductsPropertiesValues;
use app\models\tables\ProductsImages;
use app\models\tables\Categories;

class Products extends Base
{

    public function rules()
    {
        return [
            [['name', 'sort', ], 'required'],
            [['sort', 'is_deleted', 'category_id'], 'integer'],
            [['is_deleted',  ], 'boolean'],
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

    public function getProductSizePrices()
    {
        return $this->hasMany(SizePrice::class, ['product_id' => 'id']);
    }
}
