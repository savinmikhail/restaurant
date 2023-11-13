<?php

namespace app\models\tables;

use app\models\Base;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

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
            [['order_ids', 'sum', 'created_at', 'updated_at', 'payment_method'], 'required'],
            [['order_ids', 'payment_method'], 'string'],
            [['sum'], 'number'],
        ];
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'), // Or 'value' => time() for Unix timestamp
            ],
        ];
    }
  
}
