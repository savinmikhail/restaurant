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
    public $is_deleted;
    public $visibility;
    public $quantity;
    public $image;
    public $images;
    public $is_popular;
    public $removeImage;
    public $removeMainImage;
    public $properties;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'sort', 'category_id',], 'required'],
            ['properties', 'each',  'rule' => ['each', 'rule' => ['string']]],
            ['images', 'each',  'rule' => ['each', 'rule' => ['string']]],
            [['sort', 'is_deleted', 'category_id', 'quantity',], 'integer'],
            [['visibility','is_popular', 'is_deleted',], 'boolean'],
            [['description', 'name', 'external_id'], 'string'],
            [['image'], 'string', 'skipOnEmpty' => true],
        ];
    }
}
