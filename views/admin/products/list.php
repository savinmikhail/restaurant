<?php
use yii\bootstrap4\LinkPager;
use yii\helpers\Html;

$this->title = 'Товары'; 
?>

<form method="POST" action="/admin/product/filter" enctype="multipart/form-data">
<div class="container mb-4">
    <div class="search">
        <div class="row">
            <div class="col-md-4">
                <div class="search-1">
                    <label for="category-select">Категория</label>
                    <select id="category-select" class="form-control" name="category_id">
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $id => $name): ?>
                            <option value="<?= $id ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <div class="search-2">
                    <label for="product-select">Товар</label>
                        <i class="bx bxs-map" id="product-select"></i>
                        <input type="text" id="product-select" name="product_name" class="form-control" placeholder="Введите наименование товара">
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
            <th>Базовая цена</th>
            <th>Сортировка</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $k => $item) { ?>
            <tr>
                <td>
                    <?= $item->id; ?>
                </td>
                <td>
                    <?= ($item->active == 1) ? 'Да' : 'Нет'; ?>
                </td>
                <td>
                    <img src="<?= $item->image ?: 'noimage.jpg'; ?>" width=100>
                </td>
                <td>
                    <?= $item->name; ?>
                </td>
                <td>
                    <?= $item->base_price; ?>
                </td>
                <td>
                    <?= $item->sort; ?>
                </td>
                <td>
                    <a href="/admin/product/edit/?id=<?= $item->id; ?>">Редактировать</a><br />
                    <a href="/admin/product/delete/?id=<?= $item->id; ?>"
                        onclick="return confirm('Удалить акцию <?= $item->name; ?>?')">Удалить</a>
                </td>
            </tr>

        <?php } ?>
    </tbody>
</table>
<?php echo LinkPager::widget([
    'pagination' => $pages,
]);
?>