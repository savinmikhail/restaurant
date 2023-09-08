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
                    
                    <?= $form->field($model, 'active', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Активность')
                        ->checkbox(['value' => 1, 'checked' => ($model->active == 1)])->label('Активность'); ?>
                    
                    <?= $form->field($model, 'is_popular', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Популярный товар')
                        ->checkbox(['value' => 1, 'checked' => ($model->is_popular == 1)])->label('Популярный товар'); ?>
                    
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

                    <?= $form->field($model, 'base_price', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Базовая стоимость')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Базовая стоимость')]); ?>
                    
                    <?= $form->field($model, 'delivery_separately', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Раздельная доставка')
                        ->checkbox(['value' => 1, 'checked' => ($model->delivery_separately == 1)])->label('Раздельная доставка'); ?>

                    <?= $form->field($model, 'is_bonus_item', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Бонусный товар')
                        ->checkbox(['value' => 1, 'checked' => ($model->is_bonus_item == 1)])->label('Бонусный товар'); ?>
                    
                    <?= $form->field($model, 'bonus_price', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Стомость в бонусах')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Стомость в бонусах')]); ?> 

                    <?= $form->field($model, 'pack_count', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Кол-во в упаковке')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Кол-во в упаковке')]); ?> 

                    <?= $form->field($model, 'visibility', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Видимость')
                        ->checkbox(['value' => 1, 'checked' => ($model->visibility == 1)])->label('Видимость'); ?>
                    
                    <?= $form->field($model, 'express_delivery_enabled', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label(false)
                        ->checkbox(['value' => 1, 'checked' => ($model->express_delivery_enabled == 1)])->label('Доступна быстрая доставка'); ?>
                                        
                    <?= $form->field($model, 'express_delivery_price', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Стоимость экспресс-доставки')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Стоимость экспресс-доставки')]); ?>    
                    
                    <?= $form->field($model, 'cashback_percent', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('Процент кэшбека')
                        ->textInput(['placeholder' => $model->getAttributeLabel('Процент кэшбека')]); ?>    
                    
                    <?= $form->field($model, 'allow_cashback', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label(false)
                        ->checkbox(['value' => 1, 'checked' => ($model->allow_cashback == 1)])->label('Кэшбек разрешен'); ?>
                    
                    <?= $form->field($model, 'quantity', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('В наличии')
                        ->textInput(['placeholder' => $model->getAttributeLabel('В наличии шт')]); ?>    
                    
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

                        <h3>Свойства</h3>
                        <?php $i = 0; ?>
                        <?php foreach ($properties as $prop) {?>
                            <?php
                                $model->properties[$prop->id]['value'] = (isset($productPropsValues[$prop->id])) ? $productPropsValues[$prop->id]['value'] : '';
                                if ($prop->name == 'Цвет') {
                                    $model->properties[$prop->id]['hex_color'] = (isset($productPropsValues[$prop->id])) ? $productPropsValues[$prop->id]['hex_color'] : '';
                                    $model->properties[$prop->id]['is_white'] = (isset($productPropsValues[$prop->id])) ? $productPropsValues[$prop->id]['is_white'] : '';
                                }

                            ?>
                            <?= $form->field($model, 'properties['.$prop->id.'][value]', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                            ->label($prop->name)
                            ->textInput(['placeholder' => $model->getAttributeLabel($prop->name)]); ?>    
                            <?php if ($prop->name == 'Цвет') {?>

                                <?= $form->field($model, 'properties['.$prop->id.'][hex_color]', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                            ->label('#cfcfcf')
                            ->textInput(['placeholder' => $model->getAttributeLabel('HEX-код цвета')]); ?>    

                                <?= $form->field($model, 'properties['.$prop->id.'][is_white]', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{beginWrapper}{input}{error}{endWrapper}',
                            'wrapperOptions' => ['class' => 'mb-3'],
                        ])
                            ->label(false)
                            ->checkbox(['value' => 1, 'checked' => false])->label('Белый цвет'); ?>
                            <?php }?>
                        <?php }?>
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