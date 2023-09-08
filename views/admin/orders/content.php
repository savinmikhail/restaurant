<?php $this->title = 'Заказ № M' . $order['id']; 
$total = 0;
?>
<div class="container mb-4">
<h3>Информация о заказе</h3>
<div class="row">
        <div class="col-md-2">Дата создания:</div><div class="col-md-4"><?=(new Datetime($order['created']))->format('d.m.Y H:i:s')?></div>
</div>
<div class="row">
        <div class="col-md-2">Дата обновления:</div><div class="col-md-4"><?=(new Datetime($order['updated']))->format('d.m.Y H:i:s')?></div>
</div>
<div class="row">
        <div class="col-md-2">Способ оплаты:</div><div class="col-md-4"><?=($order['payment_method'] == 'cash' ? 'При получении': 'Онлайн')?></div>
</div>
<div class="row">
        <div class="col-md-2">Оплачено:</div><div class="col-md-4"><?=($order['payed'])?'Да':'Нет'?></div>
</div>
<div class="row">
        <div class="col-md-2">Экспресс-доставка:</div><div class="col-md-4"><?=($order['basket']['is_express'])?'Да':'Нет'?></div>
</div>
<div class="row">
        <div class="col-md-2">Телефон клиента:</div><div class="col-md-4"><?=($order['user']['user_login'])?></div>
</div>
<div class="row">
        <div class="col-md-2">Отменен:</div><div class="col-md-4"><?=($order['canceled']?"Да":'Нет')?></div>
</div>
<div class="row">
        <div class="col-md-2">Статус:</div><div class="col-md-4"><?=($order['status'])?></div>
</div>
<div class="row">
        <div class="col-md-2">Адрес доставки:</div><div class="col-md-4"><?=($order['address']['name'])?></div>
</div>
<div class="row">
        <div class="col-md-2">Время доставки:</div><div class="col-md-4"><?=(new Datetime($order['period_start']))->format('d.m.Y H:i')?> - <?=(new Datetime($order['period_end']))->format('H:i')?><br /><?=$order['period_comment_text']?></div>
</div>
<div class="row">
        <div class="col-md-2">Бутылей к возврату:</div><div class="col-md-4"><?=($order['return_bottles'])?></div>
</div>
<div class="row">
        <div class="col-md-2">Бонусов начислено:</div><div class="col-md-4"><?=($order['bonus_add'])?></div>
</div>
<div class="row">
        <div class="col-md-2">Бонусов списано:</div><div class="col-md-4"><?=($order['bonus_remove'])?></div>
</div>
</div>
<div class="container mb-4">
    <h3>Состав заказа</h3>
<table class="table table-bordered table-hover">
    <thead>
    <tr>
        <th>Название</th>
        <th>Количество</th>
        <th>Цена</th>
        <th>Стоимость</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($order['basket']['items'] as $k => $item) { 
        $total += $item['price'] * $item['quantity'];
        ?>
        <tr>
            <td>
                <?= $item['product']['name']; ?>
            </td>
            <td>
            <?= $item['quantity']; ?>
            </td>
            <td>
            <?= $item['price']; ?>
            </td>
            <td>
            <?= $item['price'] * $item['quantity']; ?>
            </td>
        </tr>
    <?php } ?>
    <tr><td colspan=3 style="text-align:right;">Итого:</td><td><?=$total?></td></tr>
    </tbody>
</table>
    </div>