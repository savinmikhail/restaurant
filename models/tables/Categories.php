<?php

namespace app\models\tables;

use app\models\Base;

class Categories extends Base
{
    protected $int_vals = [
        'active',
    ];

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'image', 'description', 'external_id'], 'string'],
            [['image'], 'string', 'skipOnEmpty' => true],
            [['sort', 'parent_id', 'active','is_cooler','is_water','is_bonus'], 'integer'],
        ];
    }

    protected function recordTableName()
    {
        return 'categories';
    }

    public function getParentCat()
    {
        return $this->hasOne(Categories::class, ['id' => 'parent_id'])->one();
    }

    public function getParent()
    {
        return $this->hasOne(Categories::class, ['id' => 'parent_id']);
    }

    protected function prefixName()
    {
        return 'categories';
    }
}
