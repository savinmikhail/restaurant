<?php

namespace app\controllers;

use Yii;
use app\models\forms\LoginForm;

class AdminController extends BaseController
{
    protected $dbClassPath = '';

    public function init()
    {
        parent::init();

        $this->layout = 'main';
    }

    public function runAction($id, $params = [])
    {
        if (!parent::isAdmin()) {
            $id = 'login';
        }

        return parent::runAction($id, $params = []);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    
    public function actionLogin()
    {
        $model = new LoginForm();
        $teamplate = '/admin/login';

        if (Yii::$app && isset(Yii::$app->user) && Yii::$app->user && Yii::$app->user->identity && Yii::$app->user->identity->getId()) {
            if (parent::isAdmin()) {
                $teamplate = 'welcome';
            }
        } else {
            $post = Yii::$app->request->post();

            if ($post && isset($post['LoginForm']) && isset($post['LoginForm']['username']) && $post['LoginForm']['username']) {
                $lu = \app\models\User::find()->andWhere([
                    'user_login' => $post['LoginForm']['username'],
                ])->select('user_role')->asArray()->one();

                if ($lu && $lu['user_role'] == 'ADMIN') {
                    if ($model->load($post) && $model->login()) {
                        $teamplate = 'welcome';
                    }
                }
            }
        }

        return $this->render($teamplate, ['model' => $model]);
    }
}
