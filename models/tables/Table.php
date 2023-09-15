<?php

namespace app\models\tables;

use app\models\Base;

class Table extends Base
{
    public function rules()
    {
        return [
            [['is_deleted', 'table_number'], 'required'],
            [['table_number', 'seating_capacity', 'revision'], 'integer'],
            [['is_deleted'], 'boolean'],
            [['name'], 'string'],

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
