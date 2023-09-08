<?php

?>
<?php $this->title = 'Текстовые страницы'; ?>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>CODE</th>
            <th>Заголовок</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $k => $item) { ?>
            <tr>
                <td>
                    <?= $item->id; ?>
                </td>
                <td>
                    <?= $item->code; ?>
                </td>
                <td>
                    <?= $item->caption; ?>
                </td>
                <td>
                    <a href="/admin/pages/edit/?id=<?= $item->id; ?>">Редактировать</a><br />
                    
                </td>
            </tr>

        <?php } ?>
    </tbody>
</table>
