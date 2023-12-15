<?php

namespace app\Services\api\user_app\import_helpers;

use app\models\tables\Group;

class GroupHelper extends BaseHelper implements ImportHelperInterface
{
    public function process(array $data)
    {
        $this->processGroups($data['groups']);
    }
    private function processGroups(array $arGroups)
    {
        foreach ($arGroups as $arGroup) {
            $this->processGroup($arGroup);
        }
    }
    private function processGroup(array $arGroup)
    {
        $obGroup = Group::find()->where(['external_id' => $arGroup['id']])->one();
        if (!$obGroup) {
            $obGroup = new Group();
        }
        $obGroupValues = [
            'external_id' => $arGroup['id'],
            'name' => $arGroup['name'],
            'is_deleted' => $arGroup['isDeleted']
        ];
        $obGroup->load($obGroupValues, '');
        if (!$obGroup->save()) {
            $this->handleError('Group', $obGroup);
        }
    }
}