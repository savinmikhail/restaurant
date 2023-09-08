<?php

namespace app\controllers\api;

use app\controllers\ApiController;
use app\models\AddressForm;
use app\models\PassportForm;
use app\models\RegisterUserForm;
use app\models\tables\AddressTariffs;
use app\models\tables\Notifications;
use app\models\tables\User;
use app\models\tables\UserAddress;
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
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionIndex()
    {
        $obUser = User::findIdentity(Yii::$app->user->identity->getId());

        return $this->asJson(['success' => true, 'user' => $obUser]);
    }

    /**
     * @SWG\Post(path="/api/user/remove",
     *     tags={"User"},
     *      @SWG\Parameter(
     *      name="reason",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="reason_comment",
     *      in="formData",
     *      type="string"
     *      ),
     *      description="Удаление пользователя",
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionRemove()
    {
        $request = Yii::$app->request;
        $mail = new Mail;
        $result = $mail->sendUserRemoveMail($request);

        $obUser = User::findIdentity(Yii::$app->user->identity->getId());
        $obUser->delete();

        return $this->asJson(['success' => true, 'mailed' => $result]);
    }

    /**
     * @SWG\Post(path="/api/user/update",
     *     tags={"User"},
     *      @SWG\Parameter(
     *      name="user[user_login]",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="user[user_email]",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="user[user_birthday]",
     *      in="formData",
     *      type="string",
     *      description="Y-m-d",
     *      ),
     *      @SWG\Parameter(
     *      name="user[user_gender]",
     *      in="formData",
     *      type="string",
     *      description="M / F",
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionUpdate()
    {
        $obUser = User::findIdentity(Yii::$app->user->identity->getId());
        $request = Yii::$app->request;
        $data = $request->post('user');
        $data['user_phone'] = $data['user_login'];
        $existUser = User::find()->where(['user_login'=>$data['user_login']])->asArray()->one();
        if ($existUser && isset($existUser['user_id'])) {
            if ($existUser['user_id'] > 0 && $existUser['user_id'] != Yii::$app->user->identity->getId()) {
                return $this->asJson(['success' => false, 'errors' => ['phone'=>'Пользователь с таким номером телефона уже существует.']]);
            }
        }
        $obUser->load($data, '');
        if ($obUser->save()) {
            if ($obUser->external_id) {
                $client = new \app\common\ErpClient();
                $result = $client->updateUserProfile($obUser);
            }

            return $this->asJson(['success' => true, 'user' => $obUser]);
        } else {
            return $this->asJson(['success' => false, 'errors' => $obUser->errors]);
        }
    }

    /**
     * @SWG\Post(path="/api/user/tariffs",
     *     tags={"User"},
     *      @SWG\Parameter(
     *      name="address_id",
     *      in="formData",
     *      type="integer"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Тарифы пользователя по адресу",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionTariffs()
    {
        $request = Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['result' => false, 'errors' => ['request' => ['message' => 'Method Not Allowed', 'code' => '405']]]);
        }
        $arTariffs = AddressTariffs::find()->joinWith('tariff')->where(['address_id' => $request->post('address_id')])->asArray()->all();

        return $this->asJson(['result' => true, 'items' => $arTariffs]);
    }

    /**
     * @SWG\Post(path="/api/user/notifications",
     *     tags={"User"},
     *      @SWG\Parameter(
     *      name="from",
     *      in="formData",
     *      type="integer"
     *      ),
     *      @SWG\Parameter(
     *      name="limit",
     *      in="formData",
     *      type="integer"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Уведомления",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionNotifications()
    {
        $result = [];
        $request = Yii::$app->request;
        $from = (($request->post('from'))) ? intval($request->post('from')) : 0;
        $limit = (($request->post('limit'))) ? intval($request->post('limit')) : 20;

        $result = Notifications::find()
            ->where(['or',
            ['user_id' => $this->getUser()->getId()],
            ['user_id' => 0],
        ])
            ->addOrderBy(['created' => SORT_DESC]);
        $count = $result->count();

        $result->offset($from);
        $result->limit($limit);

        return $this->asJson(['count' => $count, 'items' => $result->asArray()->all()]);
    }

    /**
     * @SWG\Post(path="/api/user/notificationscnt",
     *     tags={"User"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Уведомления",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionNotificationscnt()
    {
        $result = [];
        $result = Notifications::find()
            ->where(['or',
            ['user_id' => $this->getUser()->getId()],
            ['user_id' => 0],
    ])->count();

        return $this->asJson(['count' => $result]);
    }

    public function actionActive()
    {
        $user_id = $this->getReqParam('user_id');

        $user = \app\models\tables\User::find()->amdWhere([
            'user_id' => $user_id,
        ]);

        if ($user) {
            $user->is_active = 1;
            $user->save();

            return $this->asJson(['status' => 'ok']);
        }

        return $this->asJson(['status' => 'error', 'errors' => ['user' => 'not_found']]);
    }

    public function actionUnactive()
    {
        $user_id = $this->getReqParam('user_id');

        $user = \app\models\tables\User::find()->amdWhere([
            'user_id' => $user_id,
        ]);

        if ($user) {
            $user->is_active = 0;
            $user->save();

            return $this->asJson(['status' => 'ok']);
        }

        return $this->asJson(['status' => 'error', 'errors' => ['user' => 'not_found']]);
    }

    /**
     * @SWG\Post(path="/api/user/registration",
     *     tags={"User"},
     *     summary="User registration.",
     *     @SWG\Parameter(
     *         name="user_email",
     *         in="formData",
     *         description="E-mail",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_type",
     *         in="formData",
     *         description="Тип пользователя",
     *         required=true,
     *         type="string",
     *         default="individual",
     *         enum={"individual","individual_as_company","company"}
     *     ),
     *     @SWG\Parameter(
     *         name="user_phone",
     *         in="formData",
     *         description="Телефон",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_first_name",
     *         in="formData",
     *         description="Имя",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_last_name",
     *         in="formData",
     *         description="Фамилия",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_sur_name",
     *         in="formData",
     *         description="Отчество",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_gender",
     *         in="formData",
     *         description="Пол M / F",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_birthday",
     *         in="formData",
     *         description="Дата рождения yyyy-mm-dd",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="passport[serial]",
     *         in="formData",
     *         description="паспорт срерия",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="passport[num]",
     *         in="formData",
     *         description="паспорт номер",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="passport[date]",
     *         in="formData",
     *         description="паспорт Date",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="passport[publisher]",
     *         in="formData",
     *         description="паспорт кем выдан",
     *         required=true,
     *         type="string"
     *     ),
     *
     *      @SWG\Parameter(
     *      name="address[name]",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="address[porch]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="address[floor]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="address[room]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="address[intercom]",
     *      in="formData",
     *      type="string",
     *      description = "домофон",
     *      ),
     *      @SWG\Parameter(
     *      name="address[fias_street]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="address[fias_house]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="address[elevator]",
     *      in="formData",
     *      type="string",
     *      description = "есть лифт: 1 / 0",
     *      ),
     *      @SWG\Parameter(
     *      name="address[private]",
     *      in="formData",
     *      type="string",
     *      description = "частный дом 1 / 0",
     *      ),
     *      @SWG\Parameter(
     *      name="address[comment]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *
     *      @SWG\Parameter(
     *      name="jur_address[name]",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[porch]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[floor]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[room]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[intercom]",
     *      in="formData",
     *      type="string",
     *      description = "домофон",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[fias_street]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[fias_house]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[elevator]",
     *      in="formData",
     *      type="string",
     *      description = "есть лифт: 1 / 0",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[private]",
     *      in="formData",
     *      type="string",
     *      description = "частный дом 1 / 0",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[comment]",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[identical]",
     *      in="formData",
     *      type="integer",
     *      description = " адрес совпадает с доставкой 1 / 0",
     *      ),
     *      @SWG\Parameter(
     *      name="jur_address[notomsk]",
     *      in="formData",
     *      type="integer",
     *      description = " не томская прописка 1 / 0",
     *      ),
     *
     *      @SWG\Parameter(
     *      name="company[director]",
     *      in="formData",
     *      type="string",
     *      description = " рук-ль",
     *      ),
     *      @SWG\Parameter(
     *      name="company[contact]",
     *      in="formData",
     *      type="string",
     *      description = " контактное лицо",
     *      ),
     *      @SWG\Parameter(
     *      name="bank[rschet]",
     *      in="formData",
     *      type="integer",
     *      description = " счет",
     *      ),
     *      @SWG\Parameter(
     *      name="bank[bik]",
     *      in="formData",
     *      type="integer",
     *      description = "Бик",
     *      ),
     *      @SWG\Parameter(
     *      name="bank[korschet]",
     *      in="formData",
     *      type="string",
     *      description = "кор счет",
     *      ),
     *      @SWG\Parameter(
     *      name="bank[name]",
     *      in="formData",
     *      type="string",
     *      description = "Наименование банка",
     *      ),
     *     @SWG\Parameter(
     *         name="user_inn",
     *         in="formData",
     *         description="ИНН",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_kpp",
     *         in="formData",
     *         description="КПП",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_company_name",
     *         in="formData",
     *         description="Название компании",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/User")
     *     ),
     * )
     */
    public function actionRegistration()
    {
        $result = ['result' => true];
        $t = time();
        $request = Yii::$app->request;
        $user_type = $this->getReqParam('user_type');
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/__reg-post-'.$t.'_encoded.log',print_r($request->post(), 1),FILE_APPEND);
        
        $obConnector = new \app\common\ErpClient();
        /*
        $user_reg = \app\common\factory\UserRegistration::createObject($user_type);
        $user = $user_reg->makeRegistry();
        $user = '';
        if (!ParamsFilter::successResult($user)) {
            return $this->asJson($user);
        } else {
            $result = [
                'status' => 'ok',
                'user' => $user,
            ];
        }
         */
        $userForm = new RegisterUserForm();
        $addressForm = new AddressForm();
        $passportForm = new PassportForm();
        $jurAddressForm = new AddressForm();
        $userForm->load($request->post(), '');
        $addressForm->load($request->post(), 'address');
        $passportForm->load($request->post(), 'passport');
        $jurAddressForm->load($request->post(), 'jur_address');

        if (!$userForm->validate() || !$addressForm->validate() || !$jurAddressForm->validate()) {
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/__reg-post-'.$t.'_encoded.log',print_r(['result' => false, 'errors' => array_merge($userForm->errors, ['address' => $addressForm->errors], ['jur_address' => $jurAddressForm->errors])], 1),FILE_APPEND);
            return $this->asJson(['result' => false, 'errors' => array_merge($userForm->errors, ['address' => $addressForm->errors], ['jur_address' => $jurAddressForm->errors])]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        $obUser = new User();
        $obAddress = new UserAddress();
        $obUser->load($userForm->getAttributes(), '');
        $obUser->is_new = 0;
        if (!$obUser->save()) {
            $transaction->rollback();
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/__reg-post-'.$t.'_encoded.log',print_r($obUser->errors, 1),FILE_APPEND);
            return $this->asJson(['result' => false, 'errors' => $obUser->errors]);
        }
        //$resultRegister = ['clientcode' => '00-00019093', 'adresscode' => 'A0-00019093', 'error_code' => 0];
        $resultRegister = $obConnector->registerUser($obUser->id, array_merge($userForm->getAttributes(), ['address' => $addressForm->getAttributes()], ['jur_address' => $jurAddressForm->getAttributes()], ['passport' => $passportForm->getAttributes()]));
        if (isset($resultRegister['error_code']) && $resultRegister['error_code']) {
            $transaction->rollback();
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/__reg-post-'.$t.'_encoded.log',print_r($resultRegister, 1),FILE_APPEND);
            return $this->asJson(['result' => false, 'errors' => ['system' => $resultRegister['error_message'],'code'=>$resultRegister['error_code']]]);
        } else {
            $obUser->external_id = $resultRegister['clientcode'];
            $obUser->save();
            if (isset($resultRegister['adresscode']) && $resultRegister['adresscode']) {
                $obAddress->load($addressForm->getAttributes(), '');
                $obAddress->user_id = $obUser->id;
                $obAddress->status = ($request->post('company_name') != '') ? 'pending' : 'active';
                $obAddress->external_id = $resultRegister['adresscode'];
                $obAddress->save();
                $result['address'] = $obAddress;
            }
            $result['user'] = $obUser;
            if ($request->post('company_name') != '') {
                $result['request_is_pending'] = true;
            } else {
                Yii::$app->user->login($obUser, 3600 * 24 * 30);
            }
        }

        $transaction->commit();

        return $this->asJson($result);
    }

    /**
     * @SWG\Get(path="/api/user/logout",
     *     tags={"User"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Log out user",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->asJson(['result' => 1]);
    }

    /**
     * @SWG\Post(path="/api/user/fastregister",
     *     tags={"User"},
     *    @SWG\Parameter(
     *      name="phone",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description = "",
     *      ),
     *    @SWG\Parameter(
     *      name="email",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description = "",
     *      ),
     *    @SWG\Parameter(
     *      name="action",
     *      in="formData",
     *      type="string",
     *      required=true,
     *      description = "get - поиск юзера в базе erp и отправка смс, confirm - подтверждение смс и регистрация непосредственно. ",
     *      ),
     *    @SWG\Parameter(
     *      name="code",
     *      in="formData",
     *      type="string",
     *      description = "",
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "результат быстрой реги",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionFastregister()
    {
        $request = Yii::$app->request;
        $redirect = null;
        if (!$request->isPost || !$request->post('phone') || !$request->post('email')) {
            return $this->asJson(['result' => false, 'errors' => ['request' => ['message' => 'Method Not Allowed', 'code' => '405']]]);
        }
        $normalizedPhone = \app\common\Util::normalizePhone($request->post('phone'));
        $errors = [];
        if (!filter_var($request->post('email', FILTER_VALIDATE_EMAIL))) {
            $errors['email'] = 'Проверьте корректность email';
        }
        if (strlen($normalizedPhone) != 11) {
            $errors['phone'] = 'Проверьте корректность номера телефона';
        }
        if (!$errors) {
            $arExisterUser = User::find()->where(['user_login' => $normalizedPhone])->asArray()->one();
            if ($arExisterUser) {
                $errors['phone'] = 'Пользователь уже зарегистирирован, перейдите к авторизации';
                $redirect = 'auth';
            }
        }
        if ($errors) {
            return $this->asJson(['result' => false, 'errors' => $errors, 'redirect' => $redirect]);
        }
        $obConnector = new \app\common\ErpClient();

        if ($request->post('action') == 'get') {
            $userVars = $obConnector->findUserByPhone($normalizedPhone);
            if ($userVars['error_code'] == 0 && isset($userVars['message_code']) && $userVars['message_code']) {
                \Yii::$app->session['smscode'] = $userVars['message_code'];
            }
            if (isset($userVars['error_message']) && mb_stristr($userVars['error_message'], 'нет в базе')) {
                $userVars['redirect'] = 'registration';
                $userVars['result'] = false;
            }

            return $this->asJson($userVars);
        }

        if ($request->post('action') == 'confirm') {
            if ($request->post('code') == \Yii::$app->session['smscode']) {
                $userVars = $obConnector->getUserByPhone($normalizedPhone);
                if (!isset($userVars['xml_id'])) {
                    return $this->asJson(['result' => false, 'errors' => ['code'=>'Ошибка получения данных пользователя с внешней системы']]);
                }
                $user = new User();
                $userVars['phone'] = $normalizedPhone;
                $userVars['email'] = $request->post('email');
                $user->createUserFromErpData($userVars);
                if (isset($user->user_id) && $user->user_id) {
                    $obConnector->NotifyRegistration($user);
                    Yii::$app->user->login($user, 3600 * 24 * 30);
                    return $this->asJson(['result' => true, 'user' => $user, 'errors' => []]);
                } else {
                    return $this->asJson(['result' => false, 'errors' => ['code'=>'Не удалось авторизовать пользователя']]);
                }

            } else {
                return $this->asJson(['result' => false, 'errors' => ['code' => 'Не верный код подтверждения']]);
            }
        }

        return $this->asJson(['result' => false, 'errors' => ['request' => ['message' => 'Method Not Allowed', 'code' => '405']]]);
    }
}
