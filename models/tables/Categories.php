<?php

namespace app\models\tables;

use app\models\Base;

class Categories extends Base
{

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'image', 'description', 'external_id'], 'string'],
            [['image'], 'string', 'skipOnEmpty' => true],
            [['sort', 'is_deleted'], 'integer'],
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
