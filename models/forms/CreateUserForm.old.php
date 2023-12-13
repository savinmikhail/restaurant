<?php

namespace app\models\forms;

use app\models\User;
use Yii;
use yii\base\Model;

class CreateUserForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password'], 'string', 'min' => 5],
        ];
    }
}