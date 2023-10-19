<?php

namespace app\models;

use app\models\Base;
use yii\web\IdentityInterface;

class User extends Base implements IdentityInterface
{
    public $username;
    public $password;
    ///private $user_password;
    public $rememberMe = true;
    public static $fields = [
        'user_login' => 'Логин',
        'user_password' => 'Пароль',
    ];

    protected function recordTableName()
    {
        return 'user';
    }

    protected function prefixName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_login'], 'required'],
            [['user_login', 'user_password', ], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'userid' => 'Userid',
            'username' => 'Username',
            'password' => 'Password',
        ];
    }

    /** INCLUDE USER LOGIN VALIDATION FUNCTIONS**/

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username.
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username, $user_is_active = false)
    {
        $params = ['user_login' => $username];

        if (is_int($user_is_active)) {
            $params['user_is_active'] = $user_is_active;
        }

        return static::findOne($params);
    }

    /**
     * Finds user by phone.
     *
     * @param string $phone
     *
     * @return static|null
     */
    public static function findByPhone($phone)
    {
        $params = ['user_login' => $phone];

        return static::findOne($params);
    }

    /**
     * Finds user by password reset token.
     *
     * @param string $token password reset token
     *
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        $expire = \Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'user_reset_token' => $token,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->user_auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password.
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->user_password === md5($password);
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = md5($password);
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->user_auth_key = Security::generateRandomKey();
    }

    public function setSmsCode($code)
    {
        $this->setAttribute('auth_sms_code', $code);
    }

    public function getSmsCode()
    {
        return $this->getAttribute('auth_sms_code');
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Security::generateRandomKey().'_'.time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->user_reset_token = null;
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $result = parent::save($runValidation, $attributeNames);

        return $result;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $result = parent::afterSave($insert, $changedAttributes);

        return $result;
    }

    public function delete()
    {
        $result = parent::delete();

        return $result;
    }

    public function import($import)
    {
        $this->load([
            'user_login' => $import['phones'][count($import['phones']) - 1]['number'],
            'user_type' => $this->types[intval($import['type'])],
        ], '');
    }

}
