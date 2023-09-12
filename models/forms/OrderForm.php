<?php

namespace app\models\forms;

use app\models\tables\Basket;
use app\models\tables\Bonus;
use app\models\User;
use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class OrderForm extends Model
{
    public $basket;
    public $payment_method;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [[ 'basket', 'payment_method'], 'required'],
            [[ 'payment_method'], 'string'],
            [['basket'], 'validateBasket', 'skipOnEmpty' => true, 'skipOnError' => false],
        ];
    }

    public function validateBasket($attribute_name, $params)
    {
        $obBasket = Basket::find()->joinWith('items')->where(['baskets.id' => $this->$attribute_name])->one();
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
