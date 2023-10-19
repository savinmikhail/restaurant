<? $this->title = 'Редактирование категории'; ?>

<?php
use yii\helpers\Html;

?>
<div class="content">
    <? if ($success) { ?>
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
    <? } ?>
    <div class="row">
        <div class="col-md-4 col-sm-6 col-12"></div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <p class="login-box-msg">
                        <?= $id ? 'Редактирование' : 'Добавление' ?> категории
                    </p>

                    <?php $form = \yii\bootstrap4\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'category-form']) ?>
                    <?= $form->field($model, 'name', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3']
                    ])
                        ->label(false)
                        ->textInput(['placeholder' => $model->getAttributeLabel('Наименование')]) ?>

                    <?= $form->field($model, 'sort', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3']
                    ])
                        ->label(false)
                        ->textInput(['placeholder' => $model->getAttributeLabel('Сортировка')]) ?>


                    <?= $form->field($model, 'is_deleted', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3']
                    ])
                        ->label(false)
                        ->checkbox(['value' => 1, 'checked' => ($model->is_deleted == 0)])->label('Активность') ?>

                    <?/*= $form->field($model, 'parent_id', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3']
                    ])
                        ->dropdownList($parentCategories)->label('Родительская категория') */?>

                    <?= $form->field($model, 'description', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3']
                    ])
                        ->label(false)
                        ->textarea(['placeholder' => $model->getAttributeLabel('Описание')]) ?>

                    <?= $form->field($model, 'image', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3']
                    ])
                        ->label(false)
                        ->fileInput(['placeholder' => $model->getAttributeLabel('Изображение')]) ?>

                    <? if ($model->image) { ?>
                        <img src="<?= $model->image ?>" width="150" />
                        <?= $form->field($model, 'removeImage', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{beginWrapper}{input}{error}{endWrapper}',
                            'wrapperOptions' => ['class' => 'mb-3']
                        ])
                            ->label(false)
                            ->checkbox(['value' => 1, 'checked' => false])->label('Удалить изображение') ?>
                    <? } ?>
                    <div class="row">

                        <div class="col-4">
                            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-block']) ?>
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