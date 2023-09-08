<?php

namespace app\models;

use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class PromotionForm extends Model
{
    public $name;
    public $description;
    public $sort;
    public $image;
    public $detail_image;
    public $banner;
    public $id;
    public $on_main;
    public $active;
    public $publish;
    public $need_join;
    public $period;
    public $removeImage;
    public $removeBanner;
    public $removeDetailImage;
    public $type;
    public $product_id;
    public $external_id;
    public $product_name;
    public $discount_value;
    public $discount_type;
    public $coupon;
    public $notinapp;
    public $rule;
    public $conditional_product_id;
    public $active_from;
    public $active_to;
    public $category_id;
    public $category_name;
    public $only_new;
    public $user_type;


    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'sort','active_from', 'user_type','active_to'], 'required'],
            [['sort','publish','need_join','only_new', 'active', 'product_id', 'discount_value', 'discount_type', 'category_id'], 'integer'],
            [['on_main','notinapp'], 'boolean'],
            [['coupon'], 'each', 'rule' => ['string']],
            [['conditional_product_id'], 'each', 'rule' => ['integer']],
            [['description','rule','period','type',  'name', 'product_name', 'category_name','external_id'], 'string'],
            [['image', 'banner', 'detail_image'], 'string', 'skipOnEmpty' => true],
            [['active_from', 'active_to'], 'date', 'format' => 'php:Y-m-d'],
            ['user_type', 'each', 'rule' => ['integer']],
        ];
    }
}
