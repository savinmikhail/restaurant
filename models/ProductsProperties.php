<?php

namespace app\models;

use app\models\Base;

class ProductsProperties extends Base
{
    public function rules()
    {
        return [
            [['name', 'sort'], 'required'],
            [['name', 'external_id', 'code'], 'string'],
            [['sort'], 'integer'],
            [['in_filter'], 'boolean'],
            //[['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
            //[['line_id'], 'exist', 'skipOnError' => true, 'targetClass' => Line::class, 'targetAttribute' => ['line_id' => 'id']],
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
