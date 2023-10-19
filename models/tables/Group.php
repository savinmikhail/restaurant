<?php

namespace app\models\tables;

use app\models\Base;

class Group extends Base
{
    protected function recordTableName()
    {
        return 'groups';
    }

    protected function prefixName()
    {
        return 'groups';
    }

    public static function tableName()
    {
        return 'groups';
    }

    public function rules()
    {
        return [
            [['name',  'is_deleted', 'external_id'], 'required'],
            [['external_id', 'name'], 'string'],
            [['is_deleted'], 'boolean'],
        ];
    }
}
