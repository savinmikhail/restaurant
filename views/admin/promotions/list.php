<?php 
use yii\helpers\Html;

$this->title = 'Акции'; 
?>

<form method="POST" action="/admin/promotion/filter" enctype="multipart/form-data">
<div class="container mb-4">
    <div class="search">
        <div class="row">
        <div class="col-md-3">
                <div>
                    <div class="search-1">
                    <label for="id">Название</label>
                        <i class="bx bxs-map" id="name"></i>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Введите название акции">
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
            <th>Активность</th>
            <th>Изображение</th>
            <th>Наименование</th>
            <th>Сортировка</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($promotions as $k => $cat) { ?>
            <tr>
                <td>
                    <?= $cat->id; ?>
                </td>
                <td>
                    <?= ($cat->active == 1) ? 'Да' : 'Нет'; ?>
                </td>
                <td>
                    <img src="<?= $cat->image ?: 'noimage.jpg'; ?>" width=100>
                </td>
                <td>
                    <?= $cat->name; ?>
                </td>
                <td>
                    <?= $cat->sort; ?>
                </td>
                <td>
                    <a href="/admin/promotions/edit/<?= $cat->id; ?>">Редактировать</a><br />
                    <a href="/admin/promotion/delete/?id=<?= $cat->id; ?>"
                        onclick="return confirm('Удалить акцию <?= $cat->name; ?>?')">Удалить</a>
                </td>
            </tr>

        <?php } ?>
    </tbody>
</table>
<a href="/admin/promotion/add">Добавить акцию</a><br />