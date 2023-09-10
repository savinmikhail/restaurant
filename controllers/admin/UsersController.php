<?php

namespace app\controllers\admin;

use app\controllers\AdminController;
use app\models\User;
use app\models\UserUpdateForm;
use Yii;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class UsersController extends AdminController
{
    public function actionIndex()
    {
        $users = User::find()->all();

        $view = new yii\web\View();
        $view->title = 'Пользователи';

        return $this->render('/admin/users/list', [
            'users' => $users,
        ]);
    }

    public function actionEdit()
    {
        $id = intval($this->getReqParam('id'));
        $user = User::find()->where(['user_id' => $id])->one();
        if (!$user) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }

        return $this->editObject($user);
    }

    public function editObject($user)
    {
        $form = new UserUpdateForm();

        if ($this->request->isPost) {
            $result = false;
            $form->load($this->request->post(), 'User');
            if ($form->validate()) {
                $formAttributes = $form->getAttributes();
                $user->attributes = $formAttributes;
                $result = $user->save();
                if (!$result) {
                    return Json::encode($user->errors);
                }
            } else {
                return Json::encode($form->errors);
            }
        }

        return $this->render('/admin/users/edit', [
            'model' => $user,
            'id' => $user->user_id,
            'success' => ($this->request->isPost ? $result : (($this->getReqParam('success')) ? true : false)),
        ]);
    }

    public function actionDelete()
    {
        $id = intval($this->getReqParam('id'));
        $model = User::find()->where(['user_id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }
        $model->delete();

        return Yii::$app->response->redirect(['/admin/users']);
    }
}