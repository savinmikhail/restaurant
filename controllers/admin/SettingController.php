<?php

namespace app\controllers\admin;

use Yii;
use app\controllers\AdminController;
use app\models\tables\Setting;
use app\models\tables\Table;

class SettingController extends AdminController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $order_limit = (int) $request->post('order_limit');
        $obSetting = Setting::find()->where(['name' => 'order_limit'])->one();
        if (!$obSetting) {
            $obSetting = new Setting();
            $obSetting->name = 'order_limit';
        }
        $obSetting->value = $order_limit;
        if (!$obSetting->save()) {
            $this->sendResponse(400, "Failed to save Setting: " . print_r($obSetting->errors, true));
        }

        return $this->render('/admin/settings/view', [
            'settings' => $obSetting,
        ]);
    }
}
