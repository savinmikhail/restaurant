<?php

use yii\helpers\Html;

$this->title = 'Стол';
?>

<form method="POST" action="/admin/tables" enctype="multipart/form-data">
    <div class="container mb-4">
        <div class="search">
            <div class="row">
                <div class="col-md-3">
                    <div>
                        <div class="search-2">
                            <label for="table_number">Номер стола</label>
                            <i class="bx bxs-map" id="table_number"></i>
                            <input type="text" id="table_number" value="<?= isset($_SESSION['table_number']) ? $_SESSION['table_number'] : '' ?>" name="table_number" class="form-control" placeholder="Введите номер стола">
                        </div>
                    </div>
                </div>

                <?= Html::submitButton('Применить', ['class' => 'btn btn-primary btn-sm align-self-center mt-4']); ?>
                <a href="/admin/table/close-table"><b>Закрыть стол</b></a>
            </div>
        </div>
    </div>
</form>