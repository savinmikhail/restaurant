<?php $this->title = 'Редактирование текстовой страницы'; ?>

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
                        <?= $id ? 'Редактирование' : 'Добавление'; ?> текстовой страницы
                    </p>

                    <?php $form = \yii\bootstrap4\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'page-form']); ?>
                    <?= $form->field($model, 'code', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Код')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Код')]); ?>

                    <?= $form->field($model, 'caption', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Заголовок')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Заголовок')]); ?>

                    <?= $form->field($model, 'content', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Содержимое')
                        ->textarea(['placeholder' => $model->getAttributeLabel('Содержимое')]); ?>
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