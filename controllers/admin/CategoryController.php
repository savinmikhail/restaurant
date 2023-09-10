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
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model->delete();
        return Yii::$app->response->redirect(['/admin/categories']);
    }

    public function actionAdd()
    {
        return $this->editObject(['id' => 0]);
    }
    public function actionEdit()
    {
        $id = intval($this->getReqParam('id'));
        $category = Categories::find()->where(['id' => $id])->asArray()->one();
        if (!$category) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->editObject($category);
    }

    public function editObject($category)
    {
        $upload = new UploadForm();
        $parentCategories = Categories::find()->where(['parent_id' => 0])->asArray()->all();

        $items[0] = '--Верхний уровень--';
        foreach ($parentCategories as $cat) {
            $items[$cat['id']] = $cat['name'];
        }
        $categoryForm = new CategoryForm();

        if ($this->request->isPost) {
            $categoryForm->load($this->request->post(), 'CategoryForm');
            if ($categoryForm->validate()) {
                $result = false;
                if ($category['id'] > 0) {
                    $model = Categories::find()->where(['id' => $category['id']])->one();
                    $redirect = false;
                } else {
                    $model = new Categories();
                    $redirect = true;
                }
                $formAttributes = $categoryForm->getAttributes();
                unset($formAttributes['image']);
                $model->attributes = $formAttributes;
                $upload->image = \yii\web\UploadedFile::getInstanceByName('CategoryForm[image]');
                if ($result = $model->save()) {
                    if ($upload->validate()) {
                        $imageName = $upload->upload('cat_' . $model->id);
                        $model->image = '/upload/' . $imageName;
                        $model->save();
                    }
                    $this->deleteImage($model);

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
            'parentCategories' => $items,
            'success' => ($this->request->isPost ? $result : (($this->getReqParam('success')) ? true : false))
        ]);
    }

    public function deleteImage($category)
    {
        if(isset($_POST['CategoryForm']['removeImage'])){
            $isRemovable = $_POST['CategoryForm']['removeImage'];
            if($isRemovable == 1){
                if (file_exists($category->image)) {
                    unlink($category->image);
                }
                $category->image = '';
                $category->save();
            }
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
        $categories = Categories::find()->joinWith('parent as p')->addOrderBy(['p.sort' => SORT_ASC, 'sort' => SORT_ASC])->all();
        $view = new yii\web\View();
        $view->title = 'Категории';
        return $this->render('/admin/categories/list', [
            'categories' => $categories,
        ]);
    }
}