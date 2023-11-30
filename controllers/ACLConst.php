<?php

namespace app\controllers;

use Yii;

class ACLConst
{
    const ADMIN_ROLE  = 'ADMIN';
    const ADMIN_ROLES = [
        'ADMIN' => 'Суперадмин',
    ];

    const USER_ROLE = 'USER';

    public static function isAdmin($user = false)
    {
        if (!$user) {
            $user = Yii::$app->user->identity;
        }

        if (!$user) {
            return false;
        }

        if (isset(self::ADMIN_ROLES[$user->role])) {
            return true;
        }

        return false;
    }

    public static function isUser($user = false)
    {
        if (!$user) {
            $user = Yii::$app->user->identity;
        }

        if (!$user) {
            return false;
        }

        if ($user->role == self::USER_ROLE) {
            return true;
        }

        return false;
    }

    public static function getAuthId()
    {
        if (Yii::$app->user && Yii::$app->user->identity && Yii::$app->user->identity->getId()) {
            return Yii::$app->user->identity->getId();
        }

        return 0;
    }

    public static function getAuthRole()
    {
        if (Yii::$app->user && Yii::$app->user->identity && Yii::$app->user->identity->getId()) {
            return Yii::$app->user->identity->role;
        }

        return 0;
    }

    public static function getAuth()
    {
        if (Yii::$app && Yii::$app->user && Yii::$app->user->identity && Yii::$app->user->identity->getId()) {
            return Yii::$app->user->identity;
        }

        return null;
    }

    public static function isAuth()
    {
        if (Yii::$app->user && Yii::$app->user->identity && Yii::$app->user->identity->getId()) {
            return true;
        }

        return false;
    }
}
