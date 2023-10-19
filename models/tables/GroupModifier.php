<?php

namespace app\models\tables;

use app\models\Base;

class GroupModifier extends Base
{
    protected function recordTableName()
    {
        return 'group_modifiers';
    }

    protected function prefixName()
    {
        return 'group_modifiers';
    }

    public static function tableName()
    {
        return 'group_modifiers';
    }

    public function rules()
    {
        return [
            [['external_id'], 'required'],
            [['default_amount', 'min_amount', 'max_amount', 'free_of_charge_amount', 'product_id'], 'integer'],
            [['external_id'], 'string'],
            [['required', 'hide_if_default_amount', 'splittable', 'child_modifiers_have_min_max_restrictions'], 'boolean'],
        ];
    }

    public function getChildModifiers()
    {
        return $this->hasMany(Modifier::class, ['group_modifier_id' => 'id']);
    }
}
