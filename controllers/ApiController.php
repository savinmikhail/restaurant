<?php

namespace app\controllers;

use app\controllers\BaseController;
use Yii;

class ApiController extends BaseController
{
    public function getUser()
    {
        return Yii::$app->user->identity;
    }
}
