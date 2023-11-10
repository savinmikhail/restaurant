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
        $expectedAuthHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);

        if (!$headers->has('Authorization') || $headers->get('Authorization') !== $expectedAuthHeader) {
            // Log the unauthorized attempt
            Yii::error("Unauthorized access attempt detected. Header: " . $headers->get('Authorization'), __METHOD__);
            
            $this->sendResponse(403, 'Access denied');
        }
    }

    public function getUser()
    {
        return Yii::$app->user->identity;
    }

}
