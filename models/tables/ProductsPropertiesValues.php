<?php

namespace app\models\tables;

use app\models\Base;

class ProductsPropertiesValues extends Base
{
    public function rules()
    {
        return [
            [['product_id', 'property_id', ], 'required'],
            [['value'], 'string'],
            [['property_id', 'product_id'], 'integer'],
        ];
    }

    protected function recordTableName()
    {
        return 'products_properties_values';
    }

    protected function prefixName()
    {
        return 'products_properties_values';
    }

    public function getProperty()
    {
        return $this->hasOne(ProductsProperties::class, ['id' => 'property_id']);
    }
}
