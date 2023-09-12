<?php

namespace app\models\tables;

use app\models\Base;
use Yii;

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
        return $this->hasOne(\app\models\tables\Basket::class, ['order_id' => 'id']);
    }



    public function getTable()
    {
        return $this->hasOne(\app\models\tables\Table::class, ['id' => 'table_id']);
    }


    // быстрое создание заказа (создаем сущность таблицы и товаров в таблице)
    // public function makeFast($result, $total, $address, $is_express, $return_bottles, $bonus_add, $bonus_remove, $payment_method, $period, $period_comment, $pledge_price)
    // {
    //     $transaction = Yii::$app->db->beginTransaction();
    //     $period = json_decode($period, 1);
    //     $period_comment = json_decode($period_comment, 1);
    //     $orderVars = [
    //         'user_id' => Yii::$app->user->identity->getId(),
    //         'address_id' => $address,
    //         'payment_method' => $payment_method,
    //         'order_sum' => $total,
    //         'status' => Order::STATUS_NEW,
    //         'updated' => date('Y-m-d H:i:s'),
    //         'period_start' => date('Y-m-d H:i:s', @$period['start']),
    //         'period_end' => date('Y-m-d H:i:s', @$period['end']),
    //         'period_id' => ((isset($period['period_id'])) ? $period['period_id'] : 0),
    //         'period_comment' => ((isset($period_comment['correction_id'])) ? $period_comment['correction_id'] : ""),
    //         'period_comment_text' => (isset($period_comment['correction_name']) ? $period_comment['correction_name'] : ""),
    //     ];
    //     $this->load($orderVars, '');
    //     $orderBasket = new Basket();
    //     // $orderBasket->fuser_id = \app\models\Fuser::getUserId();
    //     $orderBasket->is_express = $is_express;
    //     $this->order_sum = $this->order_sum - $this->bonus_remove + $pledge_price;
    //     $this->save();
    //     if ($this->errors) {
    //         $transaction->rollBack();
    //         //$this->errors[] = 'cant save order';
    //         return [false, $this->errors];
    //     }
    //     $orderBasket->order_id = $this->id;
    //     $orderBasket->save();

    //     foreach ($result['items'] as $item) {
    //         $obBasketItem = new BasketItem();
    //         $obBasketItem->basket_id = $orderBasket->id;
    //         $obBasketItem->product_id = $item['product']['id'];
    //         $obBasketItem->quantity = $item['quantity'];
    //     }

    //     if ($orderBasket->errors) {
    //         $transaction->rollBack();
    //         $orderBasket->errors[] = 'cant save basket item';
    //         return [false, $orderBasket->errors];
    //     }
    //     $transaction->commit();
    //     $this->updateBonusBalance();
    //     return [true, []];
    // }


    // создание заказа
    public function make($orderVars)
    {
        $this->load($orderVars, '');

        $table = Table::find()->where(['table_number' => \Yii::$app->session->get('table_number')])->one();
        $this->table_id = $table->id;

        $orderBasket = Basket::find()->where(['baskets.id' => $orderVars['basket']])->one();
        $result = Basket::find()
            ->joinWith('items')
            ->joinWith('items.product')
            ->where(['baskets.id' => $orderVars['basket']])->cache(false)->asArray()->one();
        $basket_total = 0;
        if (isset($result['items'])) {
            $basket_total = \app\common\Util::prepareItems($result['items']);
        }

        $this->order_sum = $basket_total;
        $this->status = Order::STATUS_NEW;

        if ($success = $this->save()) {
            $orderBasket->order_id = $this->id;
            $orderBasket->save();
        }

        return $success;
    }
}
