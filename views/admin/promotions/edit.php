<?php $this->title = ($id ? 'Редактирование' : 'Добавление').' акции ' . ($id ? $model->name: ''); ?>

<?php
use yii\helpers\Html;

?>
<div class="content">
    <?php if ($success) { ?>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Успех</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                    class="fas fa-times"></i>
                            </button>
                        </div>

                    </div>

                    <div class="card-body">
                        Изменения сохранены.
                    </div>

                </div>

            </div>
        </div>
    <?php } ?>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <?php $form = \yii\bootstrap4\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'category-form']); ?>
            <div class="card">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-tabs-one-info-tab" data-toggle="pill" href="#custom-tabs-one-info" role="tab" aria-controls="custom-tabs-one-info" aria-selected="true">Акция</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-one-rules-tab" data-toggle="pill" href="#custom-tabs-one-rules" role="tab" aria-controls="custom-tabs-one-rules" aria-selected="false">Условия применения</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-one-actions-tab" data-toggle="pill" href="#custom-tabs-one-actions" role="tab" aria-controls="custom-tabs-one-actions" aria-selected="false">Действия</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-one-coupons-tab" data-toggle="pill" href="#custom-tabs-one-coupons" role="tab" aria-controls="custom-tabs-one-coupons" aria-selected="false">Купоны</a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-one-tabContent">
                        <div class="tab-pane fade active show" id="custom-tabs-one-info" role="tabpanel" aria-labelledby="custom-tabs-one-info-tab">
                        <?php
                                echo $this->render('tabs/info', [
                                'model' => $model,
                                'form'=>$form,
                                'promotionUserTypes'=>$promotionUserTypes
                            ]);
                        ?>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-one-rules" role="tabpanel" aria-labelledby="custom-tabs-one-rules-tab">
                        
                        <?php
                                echo $this->render('tabs/rules', [
                                'model' => $model,
                                'form'=>$form,
                                'discountTypes'=>$discountTypes,
                                'types'=>$types,
                                'products'=>$products,
                                'selectedProductId'=>$selectedProductId

                            ]);
                        ?>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-one-actions" role="tabpanel" aria-labelledby="custom-tabs-one-actions-tab">
                        <?php
                                echo $this->render('tabs/actions', [
                                'model' => $model,
                                'form'=>$form,
                                'discountTypes'=>$discountTypes,
                                'types'=>$types,
                                'products'=>$products,
                                'selectedProductId'=>$selectedProductId

                            ]);
                        ?>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-one-coupons" role="tabpanel" aria-labelledby="custom-tabs-one-coupons-tab">
                        <?php
                                echo $this->render('tabs/coupons', [
                                'model' => $model,
                                'form'=>$form,
                                'coupons'=>$coupons
                            ]);
                        ?>
                        </div>
                    
                    
                    </div>
                </div>
            </div>
            
            
            
            
            <div class="card collapsed-card">
                
                <div class="card-body">
                    
                    <h3>Условия наличия товаров в корзине</h3>

                    <h4>Категория товаров</h4>

                    <?= $form->field($model, 'category_id', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3']
                    ])
                        ->dropdownList($categories, ['prompt' => 'Выберите категорию товаров по акции', 'options' => [$selectedCategoryId => ['selected' => true]]])
                        ->label('Категория товаров по акции') ?>
                    <h4>Выбранные товары</h4>

                    <?php foreach ($conditionalProducts as $conditionalProduct) {?>
                    
                    <?= $form->field($model, 'conditional_product_id['.$conditionalProduct['product_id'].']', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label(false)
                        ->textInput([
                            'value' => $conditionalProduct['product_id'],
                            'placeholder' => $model->getAttributeLabel('Введите ID товара')]);?>

                    <?php } ?>

                    <?= $form->field($model, 'conditional_product_id[n0]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Товар 1')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Введите id товара')]); ?>
                    
                    <?= $form->field($model, 'conditional_product_id[n1]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Товар 2')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Введите id товара')]); ?>
                    
                    <?= $form->field($model, 'conditional_product_id[n2]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Товар 3')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Введите id товара')]); ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-4">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-block']); ?>
                </div>
            </div>
            <?php \yii\bootstrap4\ActiveForm::end(); ?>
        </div>
        <div class="col-md-4 col-sm-6 col-12"></div>
    </div>
</div>