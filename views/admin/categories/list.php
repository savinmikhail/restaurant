<? $this->title = 'Категории'; ?>
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
        <? foreach ($categories as $k => $cat) { ?>
            <tr>
                <td>
                    <?= $cat->id ?>
                </td>
                <td>
                    <?= ($cat->active == 1) ? 'Да' : 'Нет' ?>
                </td>
                <td>
                    <img src="<?= $cat->image ?: 'noimage.jpg' ?>" width=100>
                </td>
                <td>
                    <?= ($cat->getParentCat() ? $cat->getParentCat()->name . ' -> ' : '') ?>
                    <?= $cat->name ?>
                </td>
                <td>
                    <?= $cat->sort ?>
                </td>
                <td>
                    <a href="/admin/category/edit/?id=<?= $cat->id ?>">Редактировать</a><br />
                    <a href="/admin/category/delete/?id=<?= $cat->id ?>"
                        onclick="return confirm('Удалить категорию <?= $cat->name ?>?')">Удалить</a>
                </td>
            </tr>

        <? } ?>
    </tbody>
</table>