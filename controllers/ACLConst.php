<?php

namespace app\controllers;

use app\models\User;
use Yii;

class ACLConst
{
    const ADMIN_ROLE  = 'ADMIN';
    const ADMIN_ROLES = [
        'ADMIN' => 'Суперадмин',
    ];

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

    public static function getAuth(): ?User
    {
        if (Yii::$app && Yii::$app->user && Yii::$app->user->identity && Yii::$app->user->identity->getId()) {
            return Yii::$app->user->identity;
        }

        return null;
    }

}
