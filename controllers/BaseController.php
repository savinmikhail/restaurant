<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\ACLConst;
use yii\helpers\Json;
use yii\web\Response;
use Yii;

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

    protected function sendResponse(int $code, $data)
    {
        //400 статус ставлю для всех ошибок, так как фронт не может ловить 500-е
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->statusCode = $code;
        $response->content = Json::encode($data);
        $response->send();
        Yii::$app->end();
    }
}