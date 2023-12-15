<?php

namespace app\controllers;

use app\common\Request;
use yii\web\Controller;
use app\controllers\ACLConst;
use yii\helpers\Json;
use yii\web\Response;
use Yii;

class BaseController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    protected function getReqParam($name, $default = '')
    {
        return Request::getReqParam($name, $default);
    }

    public function isAdmin($user = false)
    {
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
