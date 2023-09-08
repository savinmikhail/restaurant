<?php

namespace app\models;

use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class PassportForm extends Model
{
    public $serial;
    public $num;
    public $date;
    public $publisher;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['serial', 'num', 'date', 'publisher'], 'required'],
            [['serial', 'num'], 'integer'],
            [['publisher'], 'string'],
            ['date', 'date', 'format' => 'php:Y-m-d'],
        ];
    }
}
