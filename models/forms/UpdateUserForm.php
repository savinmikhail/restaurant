<?php

namespace app\models\forms;

use yii\base\Model;

class UpdateUserForm extends Model
{
    public $user_login;
    public $user_password;

    public function rules()
    {
        return [
            [['user_login'], 'required'],
            [['user_login', 'user_password'], 'string', 'min' => 5],
        ];
    }
}
