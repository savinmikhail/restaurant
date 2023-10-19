<?php

namespace app\models\tables;

use app\models\Base;

class Setting extends Base
{
    protected function recordTableName()
    {
        return 'settings';
    }

    protected function prefixName()
    {
        return 'settings';
    }

    public static function tableName()
    {
        return 'settings';
    }

    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['name'], 'string'],
            [['value'], 'integer'],
        ];
    }
}
