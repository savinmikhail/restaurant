<?php

namespace app\Services\api\user_app\import_helpers;

use app\models\tables\PaymentType;

class PaymentTypeHelper extends BaseHelper implements ImportHelperInterface
{
    public function process(array $data)
    {
        $this->processPaymentTypes($data['paymentTypes']);
    }

    private function processPaymentTypes(array $arPaymentTypes)
    {
        foreach ($arPaymentTypes as $paymentType) {
            $this->processPaymentType($paymentType);
        }
    }

    private function processPaymentType(array $paymentType)
    {
        $obPaymentType = PaymentType::find()->where(['external_id' => $paymentType['id']])->one();
        if (!$obPaymentType) {
            $obPaymentType = new PaymentType();
            $obPaymentType->external_id = $paymentType['id'];
        }
        $arPaymentTypeVals = [
            'code' => $paymentType['code'],
            'name' => $paymentType['name'],
            'is_deleted' => $paymentType['isDeleted'],
            'payment_processing_type' => $paymentType['paymentProcessingType'],
            'payment_type_kind' => $paymentType['paymentTypeKind'],
        ];
        $obPaymentType->attributes = $arPaymentTypeVals;
        if (!$obPaymentType->save()) {
            $this->handleError('PaymentType', $obPaymentType);
        }
    }
}
