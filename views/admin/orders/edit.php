<?php
$this->title = 'Редактирование заказа';

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
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i>
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

                    <?php $form = \yii\bootstrap4\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'admin-order-form']); ?>

                    <?= $form->field($model, 'status', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->dropdownList($statuses, ['prompt' => 'Выберите категорию товаров по акции', 'options' => [$selectedStatus => ['selected' => true]]])
                        ->label('status');  ?>

                    <?= $form->field($model, 'payment_method', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->dropdownList($payments, ['prompt' => 'Выберите категорию товаров по акции', 'options' => [$selectedPayment => ['selected' => true]]])
                        ->label('payment_method');  ?>

                    <h3>Содержимое</h3>

                    <?php foreach ($basketItems as $basketItem) { ?>
                        <div class="basketItem">

                            <?
                            // dd ($basketItem, $availableProducts);
                            ?>
                            <?= $form->field($model, 'product_id[' . $basketItem['id'] . ']', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label('basketItem')
                                ->dropdownList($availableProducts, ['options' => [$basketItem['product_id'] => ['selected' => true]]]) ?>

                            <?= $form->field($model, 'product_quantity[' . $basketItem['id'] . ']', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'mb-3'],
                            ])
                                ->label('Quantity')
                                ->textInput(['value' => $basketItem['quantity']]) ?>

                            <?= $form->field($model, 'product_size[' . $basketItem['id'] . ']', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label('size')
                                ->dropdownList($availableSizes, ['options' => [$basketItem['size_id'] => ['selected' => true]]]) ?>

                        </div>

                    <?php } ?>

                    <h3>Добавить позицию</h3>

                    <?= $form->field($model, 'product_id[0]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('basketItem')
                        ->dropdownList($availableProducts,) ?>

                    <?= $form->field($model, 'product_quantity[0]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->label('Quantity')
                        ->textInput() ?>

                    <?= $form->field($model, 'product_size[0]', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group mb-3'],
                    ])
                        ->label('size')
                        ->dropdownList($availableSizes, ['options' => [$basketItem['size_id'] => ['selected' => true]]]) ?>

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