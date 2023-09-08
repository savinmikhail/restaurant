<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use Yii;
use app\models\LoginForm;
use app\common\ACLConst;

class BaseController extends Controller
{
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function beforeAction($action) {
        $user = ACLConst::getAuth();

        if ($user && !$user->remote_ip) {
            $user->remote_ip = $_SERVER['REMOTE_ADDR'];
            
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $user->remote_ip .= '_' . $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            
            $user->save();
        }
        
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    protected function getReqParam($name, $default = '')
    {
        return \app\common\Request::getReqParam($name, $default);
    }

    public function isAdmin($user = false) {
        return ACLConst::isAdmin($user);
    }
}