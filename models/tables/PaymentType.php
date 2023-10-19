<?php

namespace app\models\tables;

use app\models\Base;

class PaymentType extends Base
{
    protected function recordTableName()
    {
        return 'payment_types';
    }

    protected function prefixName()
    {
        return 'payment_types';
    }

    public static function tableName()
    {
        return 'payment_types';
    }

    public function rules()
    {
        return [
            [['code', 'name', 'is_deleted', 'payment_processing_type', 'payment_type_kind', 'external_id'], 'required'],
            [['code', 'name', 'payment_processing_type', 'payment_type_kind', 'external_id'], 'string'],
            [['is_deleted'], 'boolean'],
        ];
    }
}
