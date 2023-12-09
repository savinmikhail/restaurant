<?php

namespace app\models\tables;

use app\models\Base;
use yii\behaviors\TimestampBehavior;

class Payment extends Base
{
    protected function recordTableName()
    {
        return 'payments';
    }

    protected function prefixName()
    {
        return 'payments';
    }

    public static function tableName()
    {
        return 'payments';
    }

    public function rules()
    {
        return [
            [['order_ids', 'sum',], 'required'],
            [['order_ids', 'payment_method'], 'string'],
            [['sum', 'paid'], 'number'],
            [['created_at', 'updated_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }
}
