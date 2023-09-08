<?php

namespace app\models;

use app\models\tables\User;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class RegisterUserForm extends Model
{
    public $user_type;
    public $user_phone;
    public $user_email;
    public $user_first_name;
    public $user_last_name;
    public $user_sur_name;
    public $user_gender;
    public $user_birthday;
    public $passport;
    public $user_inn;
    public $user_kpp;
    public $user_company_name;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['user_email', 'user_type'], 'required'],
            ['user_gender', 'in', 'range' => ['F', 'M']],
            ['user_phone', 'filter', 'filter' => function ($value) {
                return \app\common\Util::normalizePhone($value);
            }],
            ['user_phone', 'checkUserForExist', 'skipOnEmpty' => false, 'skipOnError' => false],
            [['user_inn', /*'user_kpp',*/ 'user_company_name'], 'validateCompany', 'skipOnEmpty' => false, 'skipOnError' => false],
            [['user_last_name', 'user_first_name', 'user_sur_name', 'user_gender', 'user_birthday', 'passport'], 'validatePersonal', 'skipOnEmpty' => false, 'skipOnError' => false],
            [['user_sur_name'], 'validateSurname', /*'skipOnEmpty' => false,*/ 'skipOnError' => false],
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
            if ((empty($this->$attribute_name) || trim($this->$attribute_name) == '') && !$_POST['no_surname']) {
                $this->addError($attribute_name, 'Поле не должно быть пустым ' . $_POST['no_surname']);

                return false;
            }
        }
    }

    public function validatePersonal($attribute_name, $params)
    {
        if ($this->user_type == 'individual') {
            if (empty($this->$attribute_name) || $this->$attribute_name == '') {
                if ($attribute_name == 'user_sur_name' && $_POST['no_surname']) return true;
                $this->addError($attribute_name, 'Поле не должно быть пустым');

                return false;
            }
        }
    }

    public function validateCompany($attribute_name, $params)
    {
        $errors = [];
        if ($this->user_type == 'individual') {
        } else {
            if (empty($this->$attribute_name) || $this->$attribute_name == '') {
                $this->addError($attribute_name, 'Поле не должно быть пустым');

                return false;
            }
        }
    }
}
