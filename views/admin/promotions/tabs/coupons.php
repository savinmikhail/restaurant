<?php foreach ($coupons as $coupon) {?>
                        
<?= $form->field($model, 'coupon['.$coupon['id'].']', [
    'options' => ['class' => 'form-group'],
    'template' => '{beginWrapper}{input}{error}{endWrapper}',
    'wrapperOptions' => ['class' => 'input-group mb-3'],
])
    ->label(false)
    ->textInput([
        'value' => $coupon['coupon'], 
        'placeholder' => $model->getAttributeLabel('Купон')]); ?>
<?php } ?>
<?for ($i=0;$i<=5;$i++) {?>
    <?= $form->field($model, 'coupon[n'.$i.']', [
        'options' => ['class' => 'form-group'],
        'template' => '{beginWrapper}{input}{error}{endWrapper}',
        'wrapperOptions' => ['class' => 'input-group mb-3'],
    ])
        ->label('Новый купон')
        ->textInput(['placeholder' => $model->getAttributeLabel('Введите купон')]); ?>
<?}?>