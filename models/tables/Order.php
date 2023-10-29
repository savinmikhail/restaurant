<?php

namespace app\models\tables;

use app\models\Base;

class Order extends Base
{

    public function rules()
    {
        return [
            [['table_id', 'order_sum'], 'required'],
            [['external_id', 'status'], 'string'],
            [['paid',], 'integer'],
            [['payment_method'], 'in', 'range' => ['Cash', 'IikoCard', 'Card', 'External']],
            [['created_at', 'updated_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    protected function recordTableName()
    {
        return 'orders';
    }

    public static function tableName()
    {
        return 'orders';
    }

    protected function prefixName()
    {
        return 'orders';
    }

    public function getBasket()
    {
        return $this->hasOne(\app\models\tables\Basket::class, ['id' => 'basket_id']);
    }

    public function getTable()
    {
        return $this->hasOne(\app\models\tables\Table::class, ['id' => 'table_id']);
    }

    public function getPaymentType()
    {
        return $this->hasOne(\app\models\tables\PaymentType::class, ['payment_type_kind' => 'payment_method']);
    }
    
    // создание заказа
    public function make($orderVars)
    {
        $this->table_id = $orderVars['table_id'];

        $this->order_sum = $orderVars['basket_total'];
        $this->paid = 0;
        $this->basket_id = (int) $orderVars['basket_id'];

        if (!$this->save()) {
            return ['data' => ["message" => "Error occured while saving Order", 'errors' => $this->errors]];
        }

        return true;
    }
}
