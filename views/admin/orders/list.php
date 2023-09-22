<?php

use yii\bootstrap4\LinkPager;
use yii\helpers\Html;

$this->title = 'Заказы';
?>

<form method="POST" action="/admin/orders" enctype="multipart/form-data">
    <div class="container mb-4">
        <div class="search">
            <div class="row">
                <div class="col-md-2">
                    <div>
                        <div class="search-1">
                            <label for="id">ID</label>
                            <i class="bx bxs-map" id="id"></i>
                            <input type="text" id="id" name="id" class="form-control" value="<?= isset($filter['id']) ? $filter['id'] : '' ?>" placeholder="Введите ID заказа">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div>
                        <div class="search-2">
                            <label for="external_id">IIKO ID</label>
                            <i class="bx bxs-map" id="external_id"></i>
                            <input type="text" id="external_id" value="<?= isset($filter['external_id']) ? $filter['external_id'] : '' ?>" name="external_id" class="form-control" placeholder="Введите IIKO ID заказа">
                        </div>
                    </div>
                </div>

                <?= Html::submitButton('Найти', ['class' => 'btn btn-primary btn-sm align-self-center mt-4']); ?>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>IIKO ID</th>
                <th>Способ оплаты</th>
                <th>Оплачено</th>
                <th>Статус</th>
                <th>Сумма заказа</th>
                <th>Создан</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $k => $order) { ?>
                <tr>
                    <td>
                        <?= $order->id; ?>
                    </td>
                    <td>
                        <?= $order->external_id; ?>
                    </td>
                    <td>
                        <?= $order->payment_method; ?>
                    </td>
                    <td>
                        <?= ($order->payed === 1) ? 'Да' : 'Нет'; ?>
                    </td>
                    <td>
                        <?= $order->status; ?>
                    </td>
                    <td>
                        <?= $order->order_sum; ?>
                    </td>
                    <td>
                        <?= $order->created_at; ?>
                    </td>
                    <td>
                        <a href="/admin/orders/content/?id=<?= $order->id; ?>">Смотреть состав</a><br />
                        <a href="/admin/orders/edit/?id=<?= $order->id; ?>">Редактировать</a><br />
                    </td>
                </tr>

            <?php } ?>
        </tbody>
    </table>
    <?php echo LinkPager::widget([
        'pagination' => $pages,
    ]);
    ?>