<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Fuser extends ActiveRecord
{
    public static function tableName()
    {
        return 'basket_fuser';
    }

    public static $fuserId = null;
    public static $fuserCode = null;

    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string'],
            [['user_id', 'created', 'updated'], 'integer'],
        ];
    }

    public static function getUserId()
    {
        if (self::$fuserId) {
            return self::$fuserId;
        }
        $obFuser = new Fuser();
        $cookies = Yii::$app->getRequest()->getCookies();
        $userId = null;
        $obUser = Yii::$app->user->identity;
        if ($obUser) {
            $userId = $obUser->getId();
        }
        /*
        if ($userId) {
            $fuser = $obFuser->find()->where(['user_id' => $userId])->one();
            $data = ['code' => md5(time().substr(md5(rand(1000, 9999)), 0, 10)), 'created' => time(), 'updated' => time()];
            $fuser->load($data, '');
            $fuser->save();
            $obFuser->setCookies();

            return $fuser->id;
        }
*/
        if ($cookies->has('fuser_id') && $cookies->has('fuser_uid')) {
            $arFuser = $obFuser->find()->where(['id' => $cookies->getValue('fuser_id', null), 'code' => $cookies->getValue('fuser_uid', null)])->asArray()->one();
            if ($arFuser['id']) {
                return $arFuser['id'];
            }
        }
        $data = ['code' => md5(time().substr(md5(rand(1000, 9999)), 0, 10))];
        $obFuser->load($data, '');
        $obFuser->save();
        if ($obFuser->id) {
            $obFuser->setCookies();
            self::$fuserId = $obFuser->id;
            self::$fuserCode = $obFuser->code;

            return $obFuser->id;
        }
    }

    public function setCookies()
    {
        $cookies = Yii::$app->request->cookies;
        $cookies->readOnly = false;
        Yii::$app->getResponse()->getCookies()->add(new \yii\web\Cookie([
            'name' => 'fuser_id',
            'value' => $this->id,
            'sameSite' => 'None',
            'expire' => time() + 180 * 86400,
        ]));
        Yii::$app->getResponse()->getCookies()->add(new \yii\web\Cookie([
            'name' => 'fuser_uid',
            'value' => $this->code,
            'sameSite' => 'None',
            'expire' => time() + 180 * 86400,
        ]));
    }
}
