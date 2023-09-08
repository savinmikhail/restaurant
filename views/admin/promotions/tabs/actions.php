<?/*?>
<?= $form->field($model, 'discount_value', [
    'options' => ['class' => 'form-group'],
    'template' => '{beginWrapper}{input}{error}{endWrapper}',
    'wrapperOptions' => ['class' => 'input-group mb-3'],
])
    ->label(false)
    ->textInput(['placeholder' => $model->getAttributeLabel('Размер скидки')]); ?>

<?= $form->field($model, 'discount_type', [
    'options' => ['class' => 'form-group'],
    'template' => '{beginWrapper}{input}{error}{endWrapper}',
    'wrapperOptions' => ['class' => 'mb-3'],
])
    ->label(false)
    ->dropdownList($discountTypes)->label('Тип скидки') ?>
<?*/?>