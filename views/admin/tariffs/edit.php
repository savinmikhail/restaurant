<?php $this->title = 'Редактирование категории'; ?>

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
                        <?= $id ? 'Редактирование' : 'Добавление'; ?> категории
                    </p>

                    <?php $form = \yii\bootstrap4\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'category-form']); ?>
                    <?= $form->field($model, 'name', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Наименование')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Наименование')]); ?>

                    <?= $form->field($model, 'external_id', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Внешний идентификатор')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Внешний идентификатор')]); ?>

                    <?= $form->field($model, 'sort', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Сортировка')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Сортировка')]); ?>


                    <?= $form->field($model, 'active', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Активность')
                        ->checkbox(['value' => 1, 'checked' => ($model->active == 1)])->label('Активность'); ?>

                    <?= $form->field($model, 'is_white', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Белый')
                        ->checkbox(['value' => 1, 'checked' => ($model->is_white == 1)])->label('Белый'); ?>

                    <?= $form->field($model, 'category_id', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->dropdownList($tariffCategories)->label('Категория'); ?>
                    <label>Типы пользователей</label>
                    <?php $model->user_type = [];
               
                    if (is_array($tariffUsertypes)) {
                        foreach ($tariffUsertypes as $tu) {
                            $model->user_type[] = $tu['user_type'];
                        }
                    }
                    ?>
                    <?php
                    for ($user_type = 1; $user_type <= 3; ++$user_type) {?>
                        <?= $form->field($model, 'user_type['.$user_type.']', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->checkbox(['value' => 1, 'checked' => (bool)in_array($user_type, $model->user_type)])->label(\app\models\tables\User::$user_types[$user_type]['name']); ?>
                        <?php
                    }?>
                    
                    <?= $form->field($model, 'description', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Описание')
                        ->textarea(['placeholder' => $model->getAttributeLabel('Описание')]); ?>

                    <?= $form->field($model, 'need_documents', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Необходимы документы')
                        ->checkbox(['value' => 1, 'checked' => ($model->need_documents == 1)?true:false])->label('Необходимы документы'); ?>

                    <?= $form->field($model, 'document_description', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Описание документов')
                        ->textarea(['placeholder' => $model->getAttributeLabel('Описание документов')]); ?>

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