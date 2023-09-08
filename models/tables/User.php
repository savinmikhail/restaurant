<?php

namespace app\models\tables;

use app\models\Base;
use yii\web\IdentityInterface;

class User extends Base implements IdentityInterface
{
    public $username;
    public $password;
    ///private $user_password;
    public $rememberMe = true;
    private $types = [1 => 'individual', 2 => 'company', 3 => 'individual_as_company'];
    public static $fields = [
        'user_phone' => 'Телефон',
        'user_login' => 'Логин',
        'user_first_name' => 'Имя',
        'user_last_name' => 'Фамилия',
        'user_sur_name' => 'Отчетсво',
        'user_type' => 'Тип пользователя',
        'user_email' => 'E-mail',
        'user_inn' => 'ИНН',
        'user_kpp' => 'КПП',
        'user_company_name' => 'Наименование организации',
        'user_password' => 'Пароль',
    ];

    public static $user_types = [
        1 => ['code' => 'individual', 'name' => 'Частное лицо'],
        2 => ['code' => 'company', 'name' => 'Компания'],
        3 => ['code' => 'individual_as_company', 'name' => 'Частное лицо как компания'],
    ];
    protected $default_vals = [
        'user_is_active' => 0,
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
            [['user_login', 'user_password', 'user_email'], 'string', 'max' => 255],
            [['user_birthday', 'user_gender', 'user_last_name', 'user_first_name', 'user_sur_name', 'external_id', 'user_type', 'user_phone'], 'string'],
            [['pledge_client', 'pledge_bottles','disallow_order','allow_correct','is_new', 'user_is_active'], 'integer'],
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
        if ($this->user_phone && !$this->user_login) {
            $this->user_login = $this->user_phone;
        }

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
            'external_id' => $import['xml_id'],
            'user_first_name' => $import['name']['first_name'],
            'user_last_name' => $import['name']['last_name'],
            'user_sur_name' => $import['name']['middle_name'],
            'user_login' => $import['phones'][count($import['phones']) - 1]['number'],
            'user_is_active' => intval(!intval($import['rule']['cant_make'])),
            'user_phone' => isset($import['phones'][0]['number']) ? $import['phones'][0]['number'] : '',
            'user_type' => $this->types[intval($import['type'])],
            'pledge_client' => ($import['rule']['pledge_client'] == true)?1:0,
            'pledge_bottles' => $import['rule']['pledge_bottles'],
            'is_new' => isset($import['rule']['new_client'])?($import['rule']['new_client']?1:0):0,
            'disallow_order'=>(isset($import['rule']['cant_make']))?($import['rule']['cant_make']?1:0):0,
            'allow_correct'=>(isset($import['rule']['periodmark_available'])) ?($import['rule']['periodmark_available']?1:0):0
        ], '');
    }

    public function importAddressByExternalId($address)
    {
        $obAddress = UserAddress::find()->where(['external_id' => $address['address_code']])->one();
        if (!$obAddress) {
            $obAddress = new UserAddress();
        }
        $obAddress->load([
            'name' => $address['view_address'],
            'external_id' => $address['address_code'],
            'user_id' => $this->id,
            'status' => 'active',
            'district_id' => isset($address['district_id']) ? $address['district_id'] : 0,
            'fias_street' => isset($address['fias_street']) ? $address['fias_street'] : '-',
            'fias_house' => isset($address['fias_house']) ? $address['fias_house'] : '-',
            'intercom' => isset($address['intercom']) ? $address['intercom'] : '-',
            'porch' => isset($address['porch']) ? $address['porch'] : '-',
            'floor' => isset($address['floor']) ? $address['floor'] : '-',
            'room' => isset($address['room']) ? $address['room'] : '-',
            'elevator' => isset($address['fias_house']) ? $address['fias_house'] : 0,
            'private' => (isset($address['private'])) ? $address['private'] : 0,
        ], '');
        $obAddress->save();
    }

    public function createUserFromErpData($userVars)
    {
        $this->external_id = $userVars['xml_id'];
        $this->login = $userVars['phone'];
        $this->user_phone = $userVars['phone'];
        $this->user_email = $userVars['email'];
        $this->user_is_active = 1;
        $this->is_new = 0;
        $this->user_role = 'USER';
        $this->user_type = \app\common\ErpClient::$userTypes[$userVars['type']];
        $this->user_first_name = $userVars['name']['first_name'];
        $this->user_last_name = $userVars['name']['last_name'];
        $this->user_sur_name = $userVars['name']['middle_name'];
        $this->pledge_client = $userVars['rule']['pledge_client'];
        $this->pledge_bottles = $userVars['rule']['pledge_bottles'];
        $this->save();
        if ($this->id) {
            if (isset($userVars['address']) && is_array($userVars['address'])) {
                foreach ($userVars['address'] as $address) {
                    $this->importAddressByExternalId($address);
                }
            }
        }
    }
}
