<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class CategoryForm extends Model
{
    public $name, $description, $sort, $image, $parent_id, $id, $active, $removeImage;
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'sort'], 'required'],
            [['parent_id', 'sort', 'active'], 'integer'],
            [['description', 'name'], 'string'],
            ['image', 'string', 'skipOnEmpty' => true]
        ];
    }
}