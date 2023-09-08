<?php

namespace app\models;

use app\models\Base;

class ProductsImages extends Base
{
    public function rules()
    {
        return [
            [['image'], 'string', 'skipOnEmpty' => true],
        ];
    }

    protected function recordTableName()
    {
        return 'products_images';
    }

    protected function prefixName()
    {
        return 'products_images';
    }
}
