<?php

namespace app\models\tables;

use app\models\Base;

class ProductsProperties extends Base
{
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['name', 'code'], 'string'],
            [['sort'], 'integer'],
        ];
    }

    protected function recordTableName()
    {
        return 'products_properties';
    }

    protected function prefixName()
    {
        return 'products_properties';
    }

    public function getFacet()
    {
        return $this->hasMany(FacetIndex::class, ['property_id' => 'id']);
    }
}
