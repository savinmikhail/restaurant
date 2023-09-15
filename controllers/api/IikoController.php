<?php

namespace app\controllers\api;

use app\controllers\ApiController;
use app\Services\MenuParser;
use Exception;
use Yii;

class IikoController extends ApiController
{
    const API_KEY = 'fbe5d638-20e';

    public function actionKey()
    {
        $data = ['apiLogin' => self::API_KEY];
        $url = 'https://api-ru.iiko.services/api/1/access_token';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
        ));

        $outData = curl_exec($ch);

        curl_close($ch);

        $outData = json_decode($outData, true);
        Yii::$app->session->set('apiToken', $outData['token']);
        dd($outData['token']);
    }
    public function actionId()
    {
        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            dd('Token not found');
        }
        $url =  'https://api-ru.iiko.services/api/1/organizations';
        $data = '{}';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $outData = curl_exec($ch);

        curl_close($ch);

        $outData = json_decode($outData, true);

        if($outData){
            Yii::$app->session->set('organizationId', $outData['organizations'][0]['id']);
            return $this->asJson(['success' => true, 'data' => $outData['organizations'][0]['id']]);
        }
        return $this->asJson(['success' => false, 'data' => []]);

    }
    public function actionMenu()
    {

        $token = Yii::$app->session->get('apiToken');
        if (!$token) {
            dd('Token not found');
        }
        $organizationId = Yii::$app->session->get('organizationId');
        if (!$organizationId) {
            dd('Organization Id not found');
        }
        $url = 'https://api-ru.iiko.services/api/1/nomenclature';
        $data = ['organizationId' => $organizationId];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $outData = curl_exec($ch);
        curl_close($ch);
        $outData = json_decode($outData, true);
        if($outData){
            file_put_contents('../runtime/logs/menu.txt', serialize($outData));
            dump($outData);
        }
        return 'Token expired';
    }
    public function actionExport()
    {
        $menuData = unserialize(file_get_contents('../runtime/logs/menu.txt'));
        // try {
            $menuParser = new MenuParser();
            $menuParser->parse($menuData);
        // } catch (Exception $e) {
        //     echo 'Error: ' . $e->getMessage();
        //     exit; 
        // }
    }
}
