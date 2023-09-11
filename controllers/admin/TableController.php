<?php

namespace app\controllers\admin;

use Yii;
use \app\controllers\AdminController;
use app\models\tables\Table;

class TableController extends AdminController
{

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $table_number = (int) $request->post('table_number');
        $table = Table::find()->where(['table_number' => $table_number])->one();

        // header("table_number: $table_number");
        Yii::$app->response->headers->set('table_number', $table_number);

        Yii::$app->session->set('table_number', $table_number);

        return $this->render('/admin/table/view', [
            'table' => $table,
        ]);
    }

}