<?php

namespace app\models\tables;

use app\models\Base;

class Queue extends Base
{
    protected function recordTableName()
    {
        return 'queue';
    }

    protected function prefixName()
    {
        return 'queue';
    }

    public function rules()
    {
        return [
            [['order_id', 'retries' ], 'required'],
            [['order_id', 'retries'], 'integer']
        ];
    }

}
