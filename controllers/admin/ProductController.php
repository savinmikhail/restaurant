<?php

namespace app\controllers\admin;

use app\controllers\AdminController;
use app\models\forms\ProductForm;
use app\models\tables\Categories;
use app\models\tables\Products;
use app\models\tables\ProductsImages;
use app\models\tables\ProductsPropertiesValues;
use app\models\forms\UploadForm;
use Yii;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

class ProductController extends AdminController
{
    public function actionDelete()
    {
        $id = intval($this->getReqParam('id'));
        $model = Products::find()->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model->delete();

        return Yii::$app->response->redirect(['/admin/products']);
    }

    public function actionAdd()
    {
        return $this->editObject(['id' => 0]);
    }

    public function actionEdit()
    {
        $id = intval($this->getReqParam('id'));
        $Product = Products::find()->where(['id' => $id])->asArray()->one();
        if (!$Product) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->editObject($Product);
    }

    public function editObject($Product)
    {
        $upload = new UploadForm();
        $ProductForm = new ProductForm();
        // $productProperties = new ProductsProperties();
        // $productPropertiesValues = new ProductsPropertiesValues();
        $productImages = new ProductsImages();
        if ($this->request->isPost) {
            $ProductForm->load($this->request->post(), 'ProductForm');
            if ($ProductForm->validate()) {
                $result = true;
                $redirect = true;
                if ($Product['id'] > 0) {
                    $model = Products::find()->where(['id' => $Product['id']])->one();
                } else {
                    $model = new Products();
                }
                $formAttributes = $ProductForm->getAttributes();
                // $arProductProperties = $formAttributes['properties'];

                unset($formAttributes['properties'],$formAttributes['image'], $formAttributes['banner'],$formAttributes['detail_image']);
                $model->attributes = $formAttributes;

                $upload->image = \yii\web\UploadedFile::getInstanceByName('ProductForm[image]');
                
                if ($result = $model->save()) {
                    // $this->updateProductProperties($model, $arProductProperties);
                    if ($upload->validate()) {
                        $model->image = '/upload/' . $upload->upload('product_'.$model->id);
                        $model->save();
                    }

                    $this->deleteSelectedImages();
                    $this->updateProductGallery($model, $formAttributes['images']);
                    $this->deleteMainImage($model);

                    if ($redirect) {
                        return Yii::$app->response->redirect(['/admin/product/edit', 'id' => $model->id, 'success' => true]);
                    }
                }
                $ProductForm->load($model->attributes, '');
            }
        } else {
            $ProductForm->load($Product, '');
        }

        $parentCategories = Categories::find()->asArray()->all();

        $items[0] = '--Верхний уровень--';
        foreach ($parentCategories as $cat) {
            $items[$cat['id']] = $cat['name'];
            $cats[$cat['id']] = $cat;
        }

        return $this->render('/admin/products/edit', [
            'model' => $ProductForm,
            'id' => $Product['id'],
            'categories' => $items,
            'images' => ($Product['id'] > 0) ? $productImages->find()->where(['product_id' => $Product['id']])->all() : [],
            // 'properties' => $productProperties->find()->all(),
            // 'productPropsValues' => $productPropsVals,
            'success' => ($this->request->isPost ? $result : (($this->getReqParam('success')) ? true : false)),
        ]);
    }

    public function updateProductProperties($model, $properties)
    {
        $productPropertiesValue = new ProductsPropertiesValues();
        foreach ($properties as $property_id => $arProp) {
            $obProp = $productPropertiesValue->find()->where(['product_id' => $model->id, 'property_id' => $property_id])->one();
            if (!$obProp) {
                $obProp = new ProductsPropertiesValues();
                $obProp->product_id = $model->id;
                $obProp->property_id = $property_id;
            }
            $obProp->value = $arProp['value'];

            if ($arProp['value']) {
                $obProp->save();
            } else {
                if ($obProp->id) {
                    $obProp->delete();
                }
            }
        }
    }


    public function updateProductGallery($model, $images)
    {

        $errors = [];
        $images = (\yii\web\UploadedFile::getInstancesByName("ProductForm[images]"));
        foreach ($images as $image) {
            $upload = new UploadForm();
            $upload->image =$image;

            $tempName = $image->tempName;
            $tempNames = $_FILES['ProductForm']['tmp_name']['images'];
            $id = array_search($tempName, $tempNames);

            if (is_int($id) || ctype_digit($id)) {
                $productImage = ProductsImages::find()->where(['id'=>$id])->one();
            } else {
                $productImage = new ProductsImages();
            }

            $productImage->product_id = $model->id;
            if ($upload->validate()) {
                $productImage->save();
                $result = $upload->upload('gallery_'.$model->id.'_'.$productImage->id);
                $productImage->image = '/upload/'.$result;
                $productImage->save();
            }else {
                $errors[] = $productImage->errors;
            }
        }
        return $errors;
    }

    public function deleteSelectedImages()
    {
        $ProductForm = Yii::$app->request->post('ProductForm');
        if(isset($ProductForm['removeImage'])){
            $arrRemoveImages = $ProductForm['removeImage'];
            if(isset($arrRemoveImages) && is_array($arrRemoveImages)){
                foreach ($arrRemoveImages as $imageId => $isChecked) {
                    if ($isChecked == 1) {
                        $imageId = intval($imageId);
                        $image = ProductsImages::find()->where(['id'=>$imageId])->one();
                        if ($image) {
                            $image->delete(); 
                            if (file_exists($image->image)) {
                                unlink($image->image);
                            }
                        }
                    }
                }
            }
        }
    }
   
    
    public function deleteMainImage($product)
    {
        $ProductForm = Yii::$app->request->post('ProductForm');
        if(isset($ProductForm['removeMainImage'])){
        $isRemovable = $ProductForm['removeMainImage'];
            if(isset($isRemovable)){
                if($isRemovable == 1){
                    if (file_exists($product->image)) {
                        unlink($product->image);
                    }
                    $product->image = '';
                    $product->save();
                }
            }
        }
    }

    public function actionView($id)
    {
        echo $id;
        $Product = [];

        return $this->render('/admin/products/edit', [
            'Product' => $Product,
        ]);
    }

    /**
     * Displays Products.
     *
     * @return string
     */
    public function actionIndex()
    {
        $categories = Categories::find()->select(['id', 'name'])->asArray()->all();
        $arrCategories = [];
        foreach ($categories as $item) {
            $arrCategories[$item['id']] = $item['name'];
        }
    
        $Products = Products::find();
    
        $countProducts = clone $Products;
        $pages = new Pagination(['totalCount' => $countProducts->count(), 'pageSize' => 50]);
        $Products = $Products->offset($pages->offset)->limit($pages->limit)->addOrderBy(['sort' => SORT_ASC])->all();
    
        return $this->render('/admin/products/list', [
            'products' => $Products,
            'pages' => $pages,
            'categories' => $arrCategories,
        ]);
    }
    
    public function actionFilter()
    {
        $categories = Categories::find()->select(['id', 'name'])->asArray()->all();
        $arrCategories = [];
        foreach ($categories as $item) {
            $arrCategories[$item['id']] = $item['name'];
        }
    
        $category_id = trim(Yii::$app->request->post('category_id'));
        $product_name = trim(Yii::$app->request->post('product_name'));
    
        $Products = Products::find();
    
        if (!empty($category_id)) {
            $Products->andWhere('products.category_id=:category_id', [':category_id' => $category_id]);
        }
    
        if (!empty($product_name)) {
            $Products->andWhere('name LIKE :name', [':name' => '%' . $product_name . '%']);
        }
    
        $countProducts = clone $Products;
        $pages = new Pagination(['totalCount' => $countProducts->count(), 'pageSize' => 50]);
        $Products = $Products->offset($pages->offset)->limit($pages->limit)->addOrderBy(['sort' => SORT_ASC])->all();
    
        return $this->render('/admin/products/list', [
            'products' => $Products,
            'pages' => $pages,
            'categories' => $arrCategories,
        ]);
    }
}
