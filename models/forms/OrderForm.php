<?php

namespace app\models;

use app\models\tables\Basket;
use app\models\tables\Bonus;
use app\models\tables\User;
use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class OrderForm extends Model
{
    public $address_id;
    public $bonus_add;
    public $bonus_remove;
    public $basket;
    public $return_bottles;
    public $pledge_price;
    public $period;
    public $period_comment;
    public $payment_method;
    public $is_express;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['address_id', 'basket', 'period', 'payment_method'], 'required'],
            [['address_id', 'bonus_remove', 'bonus_add','pledge_price', 'period_comment', 'period', 'payment_method'], 'string'],
            [['is_express', 'return_bottles'], 'integer'],
            [['basket'], 'validateBasket', 'skipOnEmpty' => true, 'skipOnError' => false],
            [['return_bottles'], 'validateBottles', 'skipOnEmpty' => true, 'skipOnError' => false],
            [['bonus_remove'], 'validateBonus', 'skipOnEmpty' => true, 'skipOnError' => false],
        ];
    }

    public function validateBasket($attribute_name, $params)
    {
        $obBasket = Basket::find()->joinWith('items')->where(['basket.id' => $this->$attribute_name])->one();
        if (!count($obBasket->items)) {
            $this->addError($attribute_name, 'Ваша корзина пуста');

            return false;
        }
    }

    public function validateBottles($attribute_name, $params)
    {
        $obUser = User::find()->where(['user_id' => Yii::$app->user->identity->getId()])->asArray()->one();
        if ($this->$attribute_name > $obUser['pledge_bottles']) {
            // $this->addError($attribute_name, 'Неверное количество бутылей к возврату');

            return false;
        }

        return true;
    }

    public function validateBonus($attribute_name, $params)
    {
        $errors = [];
        if (!empty($this->$attribute_name) && $this->$attribute_name != 0) {
            $arBonus = Bonus::find()->where(['user_id' => Yii::$app->user->identity->getId()])->asArray()->one();
            if (!$arBonus) {
                $this->addError($attribute_name, 'Пользователь не участвует в бонусной программе');

                return false;
            }
            if ($arBonus['balance'] < $this->$attribute_name) {
                $this->addError($attribute_name, 'Баланса пользователя не достаточно');

                return false;
            }
        }

        return true;
    }
}
