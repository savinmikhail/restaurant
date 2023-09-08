<?= $form->field($model, 'type', [
    'options' => ['class' => 'form-group'],
    'template' => '{beginWrapper}{input}{error}{endWrapper}',
    'wrapperOptions' => ['class' => 'mb-3']
])
    ->dropdownList(['norule'=>'Без условия','custom'=>'Свое условие'])->label('Условие применения акции') ?>

<?= $form->field($model, 'rule', [
        'options' => ['class' => 'form-group'],
        'template' => '{beginWrapper}{input}{error}{endWrapper}',
        'wrapperOptions' => ['class' => 'input-group mb-6'],
    ])
        ->label(false)
        ->textarea(['placeholder' => $model->getAttributeLabel('Свое условие')]); ?>