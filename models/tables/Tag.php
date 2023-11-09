<?php

namespace app\models\tables;

use app\models\Base;

class Tag extends Base
{
    protected function recordTableName()
    {
        return 'tags';
    }

    protected function prefixName()
    {
        return 'tags';
    }

    public static function tableName()
    {
        return 'tags';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
        ];
    }
}
