<?php $this->title = 'Тарифы'; ?>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Активность</th>
            <th>Наименование</th>
            <th>Сортировка</th>
            <th>Белый</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tariffs as $k => $cat) { ?>
            <tr>
                <td>
                    <?= $cat->id; ?>
                </td>
                <td>
                    <?= ($cat->active == 1) ? 'Да' : 'Нет'; ?>
                </td>
                <td>
                    <?= $cat->name; ?>
                </td>
                <td>
                    <?= $cat->sort; ?>
                </td>
                <td>
                    <?= ($cat->is_white == 1) ? 'Да' : 'Нет'; ?>
                </td>
                <td>
                    <a href="/admin/tariffs/edit/?id=<?= $cat->id; ?>">Редактировать</a><br />
                    <a href="/admin/tariffs/delete/?id=<?= $cat->id; ?>"
                        onclick="return confirm('Удалить акцию <?= $cat->name; ?>?')">Удалить</a>
                </td>
            </tr>

        <?php } ?>
    </tbody>
</table>