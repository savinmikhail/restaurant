<?php

namespace app\models;

use yii\base\Model;

class AddressForm extends Model
{
    public $name;
    public $porch;
    public $floor;
    public $room;
    public $intercom;
    public $fias_street;
    public $fias_house;

    public $elevator;

    public $private;
    public $comment;
    public $identical;
    public $notomsk;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'fias_street', 'fias_house'], 'required'],
            [['private', 'floor', 'elevator', 'notomsk', 'identical'], 'integer'],
            [['fias_house', 'name', 'comment', 'intercom', 'room', 'porch'], 'string'],
            [['room', 'porch', 'intercom', 'floor', 'elevator'], 'validateAddress', 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    public function validateAddress($attribute_name, $params)
    {
        $errors = [];
        if ($this->private) {
        } else {
            if (empty($this->$attribute_name) && $this->$attribute_name != 0) {
                $this->addError($attribute_name, 'Поле не должно быть пустым');

                return false;
            }
        }
    }
}
