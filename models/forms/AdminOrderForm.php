<?php

namespace app\models\forms;

use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class AdminOrderForm extends Model
{
    public $status;
    public $payment_method;
    public $product_quantity;
    public $product_id;
    public $product_size;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['status', 'payment_method',], 'required'],
            [['status', 'payment_method',], 'string'],
            [['product_quantity', 'product_id', 'product_size'], 'each', 'rule' => ['integer']],
        ];
    }
}
