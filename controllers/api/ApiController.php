<?php

namespace app\controllers\api;

use app\controllers\BaseController;
use Yii;

class ApiController extends BaseController
{
    public function beforeAction($action)
    {
        $this->checkApiAccess();//проверяю есть ли у клиента доступ к апи запросам
        return parent::beforeAction($action);
    }

    protected function checkApiAccess()
    {
        $headers = Yii::$app->request->headers;

        if (!$headers->has('Authorization') || $headers->get('Authorization') !== 'admin:demodemo') { //передаются в https протоколе, могу не шифровать
            $this->sendResponse(403, 'Access denied');
        }
    }

    public function getUser()
    {
        return Yii::$app->user->identity;
    }

}
