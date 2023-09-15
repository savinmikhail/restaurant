<?php

namespace app\models\tables;

use app\models\Base;

class Price extends Base
{
    protected function recordTableName()
    {
        return 'prices';
    }

    protected function prefixName()
    {
        return 'prices';
    }

    public static function tableName()
    {
        return 'prices';
    }

    public function rules()
    {
        return [
            [['current_price', 'is_included_in_menu', 'size_price_id'], 'required'],
            [['size_price_id'], 'integer'],
            [['current_price', 'next_price'], 'number'],
            [['is_included_in_menu', 'next_included_in_menu'], 'boolean'],
            [['next_date_price'], 'date', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

}
