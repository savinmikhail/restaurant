<?
namespace app\models;

use yii\httpclient\Client;

class Push
{
    private $apikey = 'AAAAYXcBAgk:APA91bGAtWa3bUThWnaJfKCPK8JKMfxdTSLxIDSs0OQGjR1f0NZngMRbEdJqlhudIJAQaRsrh_5nU3D-QYiVCHNOnqPjS0MGGIzSixN3bcTWizasBRp7yhBCvPQWh_s3cl7NV5V1iZAK';

    public function __construct()
    {
    }

    private function getVars($token)
    {
        $arToken = \app\models\tables\Token::find()->where(['token'=>$token])->asArray()->one();
        return $arToken;
    }

    private function getTokensByUser($userId)
    {
        $arTokens = \app\models\tables\Token::find()->where(['user_id'=>$userId])->asArray()->all();
        return $arTokens;
    }

    public function pushByUserId($message, $userId,$extraData=null)
    {
        if (!$userId) {
            return false;
        }
        $arTokens = $this->getTokensByUser($userId);
        if (!$arTokens) {
            return false;
        }
        foreach ($arTokens as $arTokenVars) {
            $this->pushToDevice($message, $arTokenVars['token'],$extraData);
        }

        return true;
    }

    public function pushByToken($message, $token,$extraData=null)
    {
        return $this->pushToDevice($message, $token,$extraData);
    }

    public function pushToDevice($message, $token, $extraData = null)
    {
        return $this->sendGCM($message, $token, $extraData);
    }

    private function sendGCM($message, $id, $externalData = null)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $msgNotification = [
        'title' => 'Статус заказа',
        'body' => $message,
    ];
        if (isset($externalData['title'])) {
            $msgNotification['title'] = $externalData['title'];
            unset($externalData['title']);
        }
        $extraNotificationData = [
        'refId' => 1,
        'title' => 'title',
    ];

        if ($externalData) {
            $extraNotificationData['data'] = $externalData;
        }
        $fields = [
         'registration_ids' => [
            $id,
        ],
        'notification' => $msgNotification,
        'data' => $extraNotificationData,
    ];

        $fields = json_encode($fields);

        $headers = [
        'Authorization: key='.$this->apikey,
        'Content-Type: application/json',
    ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/push_log.txt', "\n\n".date('d.m.Y H:i:s').' | '.$fields."\n".$result."\n", FILE_APPEND);

        return $result;
    }
}
