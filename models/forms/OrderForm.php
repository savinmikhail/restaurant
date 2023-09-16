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
    public $basket_id;
    public $payment_method;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [[ 'basket_id', 'payment_method'], 'required'],
            [[ 'payment_method'], 'string'],
            [['basket_id'], 'validateBasket', 'skipOnEmpty' => true, 'skipOnError' => false],
        ];
    }

    public function validateBasket($attribute_name)
    {
        $obBasket = Basket::find()->joinWith('items')->where(['baskets.id' => $this->$attribute_name])->one();
        if (!count($obBasket->items)) {
            $this->addError($attribute_name, 'Ваша корзина пуста');
            return false;
        }
    }
}
