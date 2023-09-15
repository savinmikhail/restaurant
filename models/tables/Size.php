<?php

namespace app\models\tables;

use app\models\Base;

class Size extends Base
{
    protected function recordTableName()
    {
        return 'sizes';
    }

    protected function prefixName()
    {
        return 'sizes';
    }

    public static function tableName()
    {
        return 'sizes';
    }

    public function rules()
    {
        return [
            [['name', 'priority', 'is_default', 'external_id'], 'required'],
            [['priority',], 'integer'],
            [['external_id', 'name'], 'string'],
            [['is_default'], 'boolean'],
        ];
    }
}
