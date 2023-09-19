<?php $this->title = ($id ? 'Редактирование' : 'Добавление').' товара'; ?>

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
        <div class="col-md-4 col-sm-6 col-12"></div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <p class="login-box-msg">
                        <?= $this->title; ?>
                    </p>

                    <?php $form = \yii\bootstrap4\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'product-form']); ?>
                    <?= $form->field($model, 'external_id', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Внешний ИД')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Внешний ИД')]); ?>

                    <?= $form->field($model, 'name', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Наименование')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Наименование')]); ?>
                    
                    <?= $form->field($model, 'is_deleted', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Активность')
                        ->checkbox(['value' => 1, 'checked' => ($model->is_deleted == 0)])->label('Активность'); ?>
                    
                    <? /* $form->field($model, 'is_popular', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Популярный товар')
                        ->checkbox(['value' => 1, 'checked' => ($model->is_popular == 1)])->label('Популярный товар'); */?>
                    
                    <?= $form->field($model, 'sort', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Сортировка')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Сортировка')]); ?>

                    <?= $form->field($model, 'description', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Описание')
                        ->textarea(['placeholder' => $model->getAttributeLabel('Описание')]); ?>

                    <?= $form->field($model, 'category_id', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->dropdownList($categories)->label('Категория'); ?>

                    <?= $form->field($model, 'image', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Основное изображение')
                        ->fileInput(['placeholder' => $model->getAttributeLabel('Основное изображение')]); ?>
                    <?php if ($model->image) { ?>
                       
                        <img src="<?= $model->image; ?>" width="150" />
                        <?= $form->field($model, 'removeMainImage', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{beginWrapper}{input}{error}{endWrapper}',
                            'wrapperOptions' => ['class' => 'mb-3'],
                        ])
                            ->label(false)
                            ->checkbox(['value' => 1, 'checked' => false])->label('Удалить изображение'); ?>
                    
                    <?php } ?>
                    <h3>Галерея</h3>
                    <?php foreach ($images as $image) {?>
                        <?= $form->field($model, 'images['.$image->id.']', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Изображение')
                        ->fileInput(['placeholder' => $model->getAttributeLabel('Изображение')]); ?>
                    
                        <img src="<?= $image->image; ?>" width="150" />
                        <?= $form->field($model, 'removeImage['.$image->id.']', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{beginWrapper}{input}{error}{endWrapper}',
                            'wrapperOptions' => ['class' => 'mb-3'],
                        ])
                            ->label(false)
                            ->checkbox(['value' => 1, 'checked' => false])->label('Удалить изображение'); ?>
                    <?php } ?>
                    <?= $form->field($model, 'images[n0]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Изображение')
                        ->fileInput(['placeholder' => $model->getAttributeLabel('Изображение')]); ?>
                    
                    <?= $form->field($model, 'images[n1]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Изображение')
                        ->fileInput(['placeholder' => $model->getAttributeLabel('Изображение')]); ?>
                    
                    <?= $form->field($model, 'images[n2]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Изображение')
                        ->fileInput(['placeholder' => $model->getAttributeLabel('Изображение')]); ?>

                      
                    <div class="row">

                        <div class="col-4">
                            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-block']); ?>
                        </div>
                    </div>

                    <?php \yii\bootstrap4\ActiveForm::end(); ?>
                </div>
                <!-- /.login-card-body -->
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12"></div>
    </div>
</div>