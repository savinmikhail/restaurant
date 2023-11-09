<?php

namespace app\models\tables;

use app\models\Base;
use app\models\tables\Size;
use app\models\tables\Price;

class TagProduct extends Base
{
    protected function recordTableName()
    {
        return 'tag_product';
    }

    protected function prefixName()
    {
        return 'tag_product';
    }

    public static function tableName()
    {
        return 'tag_product';
    }

    public function rules()
    {
        return [
            [['product_id', 'tag_id'], 'required'],
            [['tag_id', 'product_id'], 'integer']
        ];
    }
    public function getProduct()
    {
        return $this->hasMany(Products::class, ['id' => 'product_id']);
    }

    public function getTag()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id']);
    }
}
