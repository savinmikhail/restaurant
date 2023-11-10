<?php

namespace app\models\tables;

use app\models\Base;

class Payment extends Base
{
    protected function recordTableName()
    {
        return 'payments';
    }

    protected function prefixName()
    {
        return 'payments';
    }

    public static function tableName()
    {
        return 'payments';
    }

    public function rules()
    {
        return [
            [['order_ids',], 'required'],
            [['order_ids'], 'string'],
        ];
    }
}
