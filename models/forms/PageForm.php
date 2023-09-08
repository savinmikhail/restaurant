<?php

namespace app\models;

use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class PageForm extends Model
{
    public $code;
    public $content;
    public $caption;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['code', 'caption'], 'required'],
            [['content', 'caption', 'code'], 'string'],
        ];
    }
}
