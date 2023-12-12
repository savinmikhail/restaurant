<?php

namespace app\controllers\admin;

use app\controllers\admin\AdminController;
use app\models\forms\CreateUserForm;
use app\models\forms\UpdateUserForm;
use app\models\User;
use Yii;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\View;

class UsersController extends AdminController
{
    public function actionIndex()
    {
        $users = User::find()->all();

        $view = new View();
        $view->title = 'Пользователи';

        return $this->render('/admin/waiters/list', [
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
        $form = new UpdateUserForm();

        if ($this->request->isPost) {
            $result = false;
            $form->load($this->request->post(), 'User');
            if ($form->validate()) {
                $formAttributes = $form->getAttributes();
                $user->user_login = $formAttributes['user_login'];
                if (!empty($formAttributes['user_password'])) {
                    $user->user_password = md5($formAttributes['user_password']);
                }
                $result = $user->save();
                if (!$result) {
                    return Json::encode($user->errors);
                }
            } else {
                return Json::encode($form->errors);
            }
        }

        return $this->render('/admin/waiters/edit', [
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

    /**
     * @SWG\Post(path="/admin/user/create",
     *     tags={"User"},
     *     summary="User registration.",
     *     @SWG\Parameter(
     *         name="user_login",
     *         in="formData",
     *         description="login",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="user_password",
     *         in="formData",
     *         description="password",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "User object response",
     *         @SWG\Schema(ref = "#/definitions/User")
     *     ),
     * )
     */
    public function actionCreate()
    {
        $result = false;
        $userForm = new CreateUserForm;

        if ($this->request->isPost) {
            $obUser = new User();
            $obUser->load($this->request->post(), 'CreateUserForm');
            $obUser->setPassword($this->request->post('CreateUserForm')['user_password']);
            $obUser->user_password = $obUser->password;
            $obUser->role = 'WAITER';
            $result = $obUser->save();
            if (!$result) {
                return $this->asJson(['success' => false, 'data' => $obUser->errors]);
            }
        }

        return $this->render('/admin/waiters/edit', [
            'model' => $userForm,
            'success' => ($this->request->isPost ? $result : (($this->getReqParam('success')) ? true : false)),
        ]);
    }

}
