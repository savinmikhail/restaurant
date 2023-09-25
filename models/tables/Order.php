<?php

namespace app\models\tables;

use app\models\Base;

class Order extends Base
{
    /*
    created - принят
    in_process - в обработке
    in_path - в пути
    completed - доставле

     0 - Отменена
     1 - Принят
     2 - В обработке (прием заявок на период доставки заблокирован для распределения и маршрутизации , но еще не назначен экспедитор)
     3 - В доставке (экспедитор назначен)
     4 - Доставлен


*/
    const STATUS_ERROR = 'error';
    const STATUS_NEW = 'new';
    const STATUS_CANCELED = 'canceled'; //0
    const STATUS_CREATED = 'created';   //1
    const STATUS_INPROCESS = 'in_process';      //2
    const STATUS_INPATH = 'in_path'; //3
    const STATUS_DONE = 'completed';         //4

    const STATUS_INT = [-2 => 'error', -1 => 'new', 0 => 'canceled', 1 => 'created', 2 => 'in_process', 3 => 'in_path', 4 => 'completed'];

    public function rules()
    {
        return [
            [['table_id', 'payment_method', 'order_sum',], 'required'], 
            [['external_id', 'status'], 'string'],
            [['payed',], 'integer'],
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
        $this->status = Order::STATUS_NEW;
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
