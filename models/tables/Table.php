<?php

namespace app\models\tables;

use app\models\Base;

class Table extends Base
{
    public function rules()
    {
        return [
            [['status', 'table_number'], 'required'],
            [['table_number'], 'integer'],
            [['status'], 'boolean'],
        ];
    }

    protected function recordTableName()
    {
        return 'tables';
    }

    protected function prefixName()
    {
        return 'table';
    }

    public static function tableName()
    {
        return 'tables';
    }
}
