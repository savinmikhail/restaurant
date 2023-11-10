<?php

namespace app\controllers\api\waiter_app;

use app\controllers\api\ApiController;
use app\models\User;
use yii;
use yii\filters\AccessControl;

class UserController extends ApiController
{
    // Отключение CSRF валидации для API запросов
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                // 'only' => ['index', 'finx'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'check-auth', 'logout',],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['login'],
                        'roles' => ['?'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    if ($action->id !== 'login') {
                        $this->sendResponse(401, ['data' => 'authorization required']);
                    }
                },
            ],
        ];
    }

    /**
     * @SWG\Get(path="/api/waiter_app/user",
     *     tags={"WaiterApp\User"},
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

        $this->sendResponse(200, ['user' => $obUser]);
    }

    /**
     * @SWG\Get(path="/api/waiter_app/user/logout",
     *     tags={"WaiterApp\User"},
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

    /**
     * @SWG\Post(path="/api/waiter_app/user/login",
     *     tags={"WaiterApp\User"},
     *      @SWG\Parameter(
     *          name="login",
     *          in="formData",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          in="formData",
     *          type="string"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "User login response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionLogin()
    {
        $username = Yii::$app->request->post('login');
        $password = Yii::$app->request->post('password');

        if(!$username || !$password) {
            $this->sendResponse(401, ['data' => 'Получены невалидные данные']);
        }
        // Поиск пользователя по имени пользователя
        $user = User::findByUsername($username);

        if (!$user || !$user->validatePassword($password)) {
            $this->sendResponse(401, ['data' => 'Неверный логин или пароль']);
        }

        // Аутентификация пользователя
        Yii::$app->user->login($user);

        $this->sendResponse(200, ['data' => 'Аутентификация прошла успешно']);
    }

    /**
     * @SWG\Get(path="/api/waiter_app/user/check-auth",
     *     tags={"WaiterApp\User"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "return true if user authorized, else return false",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionCheckAuth()
    {
        $isUserAuthorized = Yii::$app->user->isGuest;
        $this->sendResponse(200, ['data' => $isUserAuthorized]);
    }
}
