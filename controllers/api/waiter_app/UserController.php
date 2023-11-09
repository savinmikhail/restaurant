<?php

namespace app\controllers\api\waiter_app;

use app\controllers\api\ApiController;
use app\models\User;
use yii;
use yii\filters\AccessControl;

class UserController extends ApiController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                // 'only' => ['index', 'finx'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'tariffs', 'logout', 'remove', 'update', 'notifications', 'orders', 'feedback', 'tariffs', 'notificationscnt'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['registration', 'fastregister'],
                        'roles' => ['?'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    if ($action->id == 'registration') {
                        return $this->asJson(['result' => false, 'errors' => ['user' => ['message' => 'user logged in', 'code' => '403']]]);
                    } else {
                        return $this->asJson(['result' => false, 'errors' => ['user' => ['message' => 'authorization required', 'code' => '401']]]);
                    }
                },
            ],
        ];
    }

    /**
     * @SWG\Get(path="/api/user",
     *     tags={"User"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        $obUser = User::findIdentity(Yii::$app->user->identity->getId());

        return $this->asJson(['success' => true, 'user' => $obUser]);
    }

    /**
     * @SWG\Get(path="/api/user/logout",
     *     tags={"User"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->asJson(['result' => 1]);
    }
}
