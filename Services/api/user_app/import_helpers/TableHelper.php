<?php

namespace app\Services\api\user_app\import_helpers;

use app\models\tables\Table;

class TableHelper extends BaseHelper implements ImportHelperInterface
{

    public function process(array $data)
    {
        $this->processTables($data['tables']);
    }

    private function processTables(array $arTables)
    {
        foreach ($arTables as $table) {
            $this->processTable($table);
        }
    }

    private function processTable(array $table)
    {
        $obTable = Table::find()->where(['external_id' => $table['id']])->one();
        if (!$obTable) {
            $obTable = new Table();
            $obTable->external_id = $table['id'];
        }
        $arPropValues = [
            'table_number' => $table['number'],
            'name' => $table['name'],
            'seating_capacity' => $table['seatingCapacity'],
            'revision' => $table['revision'],
            'is_deleted' => $table['isDeleted'],
        ];
        $obTable->attributes = $arPropValues;
        if (!$obTable->save()) {
            self::handleError('Table', $obTable);
        }
    }
}