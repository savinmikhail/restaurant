<?php

namespace app\models\tables;

use app\models\Base;

class SizePrice extends Base
{
    protected function recordTableName()
    {
        return 'size_prices';
    }

    protected function prefixName()
    {
        return 'size_prices';
    }

    public static function tableName()
    {
        return 'size_prices';
    }

    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['size_id', 'product_id'], 'integer']
        ];
    }
    public function getPrice()
    {
        return $this->hasOne(Price::class, ['size_price_id' => 'id']);
    }
}
