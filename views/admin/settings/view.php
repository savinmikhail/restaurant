<?php

use yii\helpers\Html;

$this->title = 'Настройки';
?>

<form method="POST" action="/admin/settings" enctype="multipart/form-data">
    <div class="container mb-4">
        <div class="search">
            <div class="row">
                <div class="col-md-3">
                    <div>
                        <div class="search-2">
                            <label for="order_limit">Лимит заказа</label>
                            <i class="bx bxs-map" id="order_limit"></i>
                            <input type="text" id="order_limit" value="<?= isset($settings) ? $settings->value : 0 ?>" name="order_limit" class="form-control" placeholder="Введите лимит заказа">
                        </div>
                    </div>
                </div>

                <?= Html::submitButton('Применить', ['class' => 'btn btn-primary btn-sm align-self-center mt-4']); ?>
            </div>
        </div>
    </div>