<?php

namespace app\models\forms;

use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ProductForm extends Model
{
    public $id;
    public $external_id;
    public $sort;
    public $name;
    public $description;
    public $category_id;
    public $base_price;
    public $active;
    public $delivery_separately;
    public $is_bonus_item;
    public $bonus_price;
    public $pack_count;
    public $visibility;
    public $express_delivery_enabled;
    public $express_delivery_price;
    public $cashback_percent;
    public $allow_cashback;
    public $quantity;
    public $image;
    public $images;
    public $is_popular;
    public $need_water;
    public $removeImage;
    public $removeMainImage;
    public $properties;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'sort', 'category_id', 'base_price'], 'required'],
            [['base_price', 'express_delivery_price'], 'double'],
            ['properties', 'each',  'rule' => ['each', 'rule' => ['string']]],
            ['images', 'each',  'rule' => ['each', 'rule' => ['string']]],
            [['sort', 'active', 'category_id', 'quantity', 'cashback_percent', 'pack_count', 'bonus_price'], 'integer'],
            [['visibility','is_popular', 'active', 'delivery_separately', 'is_bonus_item', 'express_delivery_enabled', 'allow_cashback'], 'boolean'],
            [['description', 'name', 'external_id'], 'string'],
            [['image'], 'string', 'skipOnEmpty' => true],
        ];
    }
}
