<?php

namespace app\controllers;

use app\controllers\BaseController;
use Yii;
use yii\helpers\Json;
use yii\web\Response;

class ApiController extends BaseController
{
    public function getUser()
    {
        return Yii::$app->user->identity;
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
