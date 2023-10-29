<?php

namespace app\controllers\admin;

use app\models\forms\CategoryForm;
use Yii;
use app\controllers\AdminController;
use yii\web\NotFoundHttpException;
use app\models\tables\Categories;
use app\models\forms\UploadForm;

class CategoryController extends AdminController
{
    /**
     * Displays categories.
     *
     * @return string
     */
    public function actionDelete()
    {
        $id = intval($this->getReqParam('id'));
        $model = Categories::find()->where(['id' => $id])->one();
        if (!$model) {
            $this->sendResponse(404, 'The requested page does not exist.');
        }
        $model->delete();
        return Yii::$app->response->redirect(['/admin/categories']);
    }

    public function actionAdd()
    {
        return $this->editObject();
    }

    public function actionEdit()
    {
        $id = intval($this->getReqParam('id'));
        $category = Categories::find()->where(['id' => $id])->asArray()->one();
        if (!$category) {
            $this->sendResponse(404, 'The requested page does not exist.');
        }

        return $this->editObject($category);
    }

    public function editObject($category = [])
    {
        $categoryForm = new CategoryForm();

        if ($this->request->isPost) {

            $categoryForm->load($this->request->post(), 'CategoryForm');
            if ($categoryForm->validate()) {

                $result = false;
                if (isset($category['id'])) {
                    $model = Categories::find()->where(['id' => $category['id']])->one();
                    $redirect = false;
                } else {
                    $model = new Categories();
                    $redirect = true;
                }
                $formAttributes = $categoryForm->getAttributes();
                unset($formAttributes['image']);
                $model->attributes = $formAttributes;

                if ($result = $model->save()) {

                    $this->updateMainImage($model, $this->request->post());

                    if ($redirect) {
                        return Yii::$app->response->redirect(['/admin/category/edit', 'id' => $model->id, 'success' => true]);
                    }
                }
                $categoryForm->load($model->attributes, '');
            }
        } else {
            $categoryForm->load($category, '');
        }
        return $this->render('/admin/categories/edit', [
            'model' => $categoryForm,
            'id' => $category['id'],
            'success' => ($this->request->isPost ? $result : (($this->getReqParam('success')) ? true : false))
        ]);
    }

    public function updateMainImage(Categories $category, $request)
    {
        if (isset($request['CategoryForm']['removeImage']) && (int) $request['CategoryForm']['removeImage'] === 1) {
            // Удаляем картинку
            if (file_exists($category->image)) {
                unlink($category->image);
            }
            $category->image = '';
        } else {
            //добавляем картинку
            $upload = new UploadForm();
            $upload->image = \yii\web\UploadedFile::getInstanceByName('CategoryForm[image]');

            if ($upload->validate()) {
                $imageName = $upload->upload('cat_' . $category->id);
                $category->image = '/upload/' . $imageName;
            }
        }
        if (!$category->save()) {
            $this->sendResponse(400, 'Failed to save Category' . print_r($category->errors));
        }
    }

    public function actionView($id)
    {
        echo $id;
        $category = [];
        return $this->render('/admin/categories/edit', [
            'category' => $category,
        ]);
    }

    public function actionIndex()
    {
        $categories = Categories::find()->addOrderBy(['sort' => SORT_ASC])->all();
        $view = new yii\web\View();
        $view->title = 'Категории';
        return $this->render('/admin/categories/list', [
            'categories' => $categories,
        ]);
    }
}
