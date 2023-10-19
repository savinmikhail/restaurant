<?php

namespace app\models\tables;

use app\models\Base;

class Order extends Base
{

    public function rules()
    {
        return [
            [['table_id', 'payment_method', 'order_sum',], 'required'], 
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

    // создание заказа
    public function make($orderVars)
    {
        $this->load($orderVars, '');

        $obTable = Table::getTable();
        if (!$obTable)
            die(json_encode("Table number is missing"));

        $this->table_id = $obTable->id;

        $orderBasket = Basket::find()->where(['baskets.id' => $orderVars['basket_id']])->one();

        $this->order_sum = $orderBasket->basket_total;
        $this->paid = 0;
        $this->basket_id = (int) $orderVars['basket_id'];
        
        if (!$this->save()) {
            throw new \Exception("Error occured while saving Order: " . print_r($this->errors, true));
        }
        $orderBasket->order_id = $this->id;
        if (!$orderBasket->save()) {
            throw new \Exception("Error occured while saving basket: " . print_r($orderBasket->errors, true));
        }
        return true;
    }
}
