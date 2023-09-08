<?php

namespace app\models;

use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class TariffForm extends Model
{
    public $external_id;
    public $name;
    public $sort;
    public $description;
    public $active;
    public $user_type;
    public $category_id;
    public $need_documents;
    public $document_description;
    public $is_white;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'sort', 'user_type', 'category_id', 'external_id'], 'required'],
            [['sort', 'active', 'category_id', 'external_id', 'need_documents', 'is_white'], 'integer'],
            [['description', 'name', 'document_description'], 'string'],
            ['user_type', 'each', 'rule' => ['integer']],
        ];
    }
}
