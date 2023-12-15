<?php

namespace app\models\forms;

use app\models\User;
use Yii;
use yii\base\Model;

class CreateUserForm extends Model
{
    public $user_login;
    public $user_password;

    public function rules()
    {
        return [
            [['user_login', 'user_password'], 'required'],
            [['user_login', 'user_password'], 'string', 'min' => 5],
        ];
    }
}