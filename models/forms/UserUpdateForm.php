<?php

namespace app\models;

use app\common\Util;
use app\models\tables\User;
use yii\base\Model;

class UserUpdateForm extends Model
{
    public $user_email;
    public $external_id;
    public $user_login;
    public $user_is_active;
    public $user_phone;
    public $user_sur_name;
    public $user_first_name;
    public $user_last_name;
    public $user_type;


    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            ['user_is_active', 'in', 'range' => [0, 1]],
            [['external_id', 'user_login', 'user_email', 'user_phone', 'user_first_name', 'user_last_name', 'user_sur_name'], 'string'],
        ];
    }
    public function checkUserForExist($attribute_name, $params)
    {
        if (User::find()->where(['user_login' => $this->$attribute_name])->asArray()->one()) {
            $this->addError($attribute_name.'_system', 'Пользователь с телефоном '.$this->$attribute_name.' уже зарегистрирован');
        }
    }

    public function validateSurname($attribute_name, $params)
    {
        if ($this->user_type == 'individual') {
            if (empty($this->$attribute_name) && $this->$attribute_name != 0 && !$_POST['no_surname']) {
                $this->addError($attribute_name, 'Поле не должно быть пустым');

                return false;
            }
        }
    }

    public function validatePersonal($attribute_name, $params)
    {
        if ($this->user_type === 'individual') {
            if (empty($this->$attribute_name) || $this->$attribute_name == 0) {
                $this->addError($attribute_name, 'Поле не должно быть пустым');

                return false;
            }
        }
    }
}