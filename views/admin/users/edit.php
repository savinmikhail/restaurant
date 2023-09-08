<?php $this->title = 'Редактирование пользователей'; ?>

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
                        <?= $id ? 'Редактирование' : 'Добавление'; ?> пользователей
                    </p>

                    <?php $form = \yii\bootstrap4\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'user-form']); ?>
                    <?= $form->field($model, 'user_email', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('user_email')
                        ->textInput(['placeholder' => $model->getAttributeLabel('E-mail')]); ?>

                    <?= $form->field($model, 'external_id', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Внешний идентификатор')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Внешний идентификатор')]); ?>

                    <?= $form->field($model, 'user_login', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Логин')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Логин')]); ?>


                    <?= 'Тип ' . $model->user_type ?>

                    <?= $form->field($model, 'user_is_active', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Активность')
                        ->checkbox(['value' => 1, 'checked' => ($model->user_is_active === 1)])->label('Активность'); ?>

                    <?= $form->field($model, 'user_phone', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Телефон')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Телефон')]); ?>


                    <?= $form->field($model, 'user_last_name', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Фамилия')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Фамилия')]); ?>

                    <?= $form->field($model, 'user_first_name', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Имя')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Имя')]); ?>

                    <?= $form->field($model, 'user_sur_name', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Отчество')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Отчество')]); ?>

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