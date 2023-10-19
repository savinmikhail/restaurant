<?php

namespace app\models\tables;

use app\models\Base;

class Modifier extends Base
{
    protected function recordTableName()
    {
        return 'modifiers';
    }

    protected function prefixName()
    {
        return 'modifiers';
    }

    public static function tableName()
    {
        return 'modifiers';
    }

    public function rules()
    {
        return [
            [['external_id'], 'required'],
            [['default_amount', 'min_amount', 'max_amount', 'free_of_charge_amount', 'group_modifier_id', 'product_id'], 'integer'],
            [['external_id'], 'string'],
            [['required', 'hide_if_default_amount', 'splittable'], 'boolean'],
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Products::class, ['external_id' => 'external_id']);
    }
}
