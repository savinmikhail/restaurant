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
            [['order_ids', 'sum',], 'required'],
            [['order_ids', 'payment_method'], 'string'],
            [['sum'], 'number'],
            [['created_at', 'updated_at'], 'safe'], // Add this line if you want to include them in rules
        ];
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }
  
}
