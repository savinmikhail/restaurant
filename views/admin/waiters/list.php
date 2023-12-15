<? $this->title = 'Официанты'; ?>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Логин</th>
            <th>Пароль</th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($users as $user) { ?>
            <tr>
                <td>
                    <?= $user->id ?>
                </td>
                <td>
                    <?= $user->user_login ?>
                </td>
                <td>
                    <?= $user->user_password ?>
                </td>
                <td>
                    <a href="/admin/users/edit/?id=<?= $user->id ?>">Редактировать</a><br />
                    <? if ($user->user_role !== 'ADMIN') : ?>
                        <a href="/admin/users/delete/?id=<?= $user->id ?>" onclick="return confirm('Удалить официанта <?= $user->user_login ?>?')">Удалить</a>
                    <? endif ?>
                </td>
            </tr>
        <? } ?>
    </tbody>
</table>
<a href="/admin/users/create">Добавить</a><br />