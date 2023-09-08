<?php

namespace app\models;

use yii\base\Model;

class OrderUpdateForm extends Model
{

    public function rules()
    {
        return [
            [['address_id', 'payment_method'], 'required'],
        ];
    }
}