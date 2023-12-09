<?php

namespace app\Services\api\user_app\import_helpers;

use app\models\tables\Size;

class SizeHelper extends BaseHelper implements ImportHelperInterface
{
    public function process(array $data)
    {
        $this->processSizes($data['sizes']);
    }
    private function processSizes(array $arSizes)
    {
        foreach ($arSizes as $arSize) {
            $this->processSize($arSize);
        }
    }
    private function processSize(array $arSize)
    {
        $obSize = Size::find()->where(['external_id' => $arSize['id']])->one();
        if (!$obSize) {
            $obSize = new Size();
        }
        $obSizeValues = [
            'external_id' => $arSize['id'],
            'name' => $arSize['name'],
            'priority' => $arSize['priority'],
            'is_default' => $arSize['isDefault']
        ];
        $obSize->load($obSizeValues, '');
        if (!$obSize->save()) {
            $this->handleError('Size', $obSize);
        }
    }
}