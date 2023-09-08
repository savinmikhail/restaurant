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
                        <input type="text" id="id" name="id" class="form-control"  value="<?=isset($filter['id'])?$filter['id']:''?>" placeholder="Введите ID заказа">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div>
                    <div class="search-2">
                    <label for="external_id">ERP ID</label>
                        <i class="bx bxs-map" id="external_id"></i>
                        <input type="text" id="external_id"   value="<?=isset($filter['external_id'])?$filter['external_id']:''?>"  name="external_id" class="form-control" placeholder="Введите ERP ID заказа">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <div class="search-3">
                    <label for="phone">Телефон</label>
                        <i class="bx bxs-map" id="phone"></i>
                        <input type="text" id="phone" name="phone"   value="<?=isset($filter['phone'])?$filter['phone']:''?>"  class="form-control" placeholder="Введите номер телефона заказчика">
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
        <th>ERP ID</th>
        <th>Способ оплаты</th>
        <th>Оплачено</th>
        <th>Статус</th>
        <th>Телефон</th>
        <th>Сумма заказа</th>
        <th>Создан</th>
        <th>Дата доставки</th>
        <th>Уточнение</th>
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
                <?= $order->user->user_phone;  ?> 
            </td>
            <td>
                <?= $order->order_sum; ?>
            </td>
            <td>
                <?= $order->created; ?>
            </td>
            <td>
                <?= (new DateTime($order->period_start))->format('d.m.Y H:i');?>
                - <?= (new DateTime($order->period_end))->format('H:i');?>

            </td>
            <td>
                <?= $order->period_comment_text; ?>
            </td>
            <td>
                <a href="/admin/order/<?= $order->id; ?>">Смотреть состав</a><br />
            </td>
        </tr>

    <?php } ?>
    </tbody>
</table>
<?php echo LinkPager::widget([
    'pagination' => $pages,
]);
?>