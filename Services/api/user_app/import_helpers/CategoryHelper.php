<?php

namespace app\Services\api\user_app\import_helpers;

use app\models\tables\Categories;

class CategoryHelper extends BaseHelper  implements ImportHelperInterface
{
    public function process(array $data)
    {
        $this->processCategories($data['categories']);
    }

    private function processCategories(array $arCategories)
    {
        foreach ($arCategories as $arCategory) {
            $this->processCategory($arCategory);
        }
    }

    private function processCategory(array $arCategory)
    {
        $obCategory = Categories::find()->where(['external_id' => $arCategory['id']])->one();
        if (!$obCategory) {
            $obCategory = new Categories();
        }
        $obCategoryValues = [
            'external_id' => $arCategory['id'],
            'name' => $arCategory['name'],
            'is_deleted' => (int) $arCategory['isDeleted']
        ];
        $obCategory->load($obCategoryValues, '');
        if (!$obCategory->save()) {
            $this->handleError('Category', $obCategory);
        }
    }
}