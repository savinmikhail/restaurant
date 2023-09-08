<?php $this->title = 'Пользователи'; ?>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Внешний ключ</th>
            <th>Логин</th>
            <th>Email</th>
            <th>Активность</th>
            <th>Телефон</th>
            <th>ФИО</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $k => $user) { ?>
            <tr>
                <td>
                    <?= $user->id; ?>
                </td>
                <td>
                    <?= $user->external_id; ?>
                </td>
                <td>
                    <?= $user->user_login; ?>
                </td>
                <td>
                    <?= $user->user_email; ?>
                </td>
                <td>
                    <?= ($user->user_is_active == 1) ? 'Да' : 'Нет'; ?>
                </td>
                <td>
                    <?= $user->user_phone; ?>
                </td>
                <td>
                    <?= $user->user_last_name . ' ' . $user->user_first_name . ' ' . $user->user_sur_name; ?>
                </td>
                <td>
                    <a href="/admin/users/edit/?id=<?= $user->user_id; ?>">Редактировать</a><br />
                    <a href="/admin/users/delete/?id=<?= $user->user_id; ?>"
                        onclick="return confirm('Удалить пользователя <?= $user->user_id; ?>?')">Удалить</a>
                </td>
            </tr>

        <?php } ?>
    </tbody>
</table>