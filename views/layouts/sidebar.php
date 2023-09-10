<?php
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/admin/" class="brand-link">
        <img src="/upload/logo.png" alt="Ключевая вода APP" class="brand-image img-circle elevation-3"
            style="opacity: 1;background-color:#fff;">
        <span class="brand-text font-weight-light">Ключевая Вода</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" class="d-block">
                    <?if (Yii::$app->user->isGuest) {?>
                        <?} else {?>
                    <?= Yii::$app->user->getIdentity()->__get('user_login'); ?>
                    <?}?>
                </a>
            </div>
        </div>



        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    ['label' => 'Login', 'url' => ['admin/login'], 'icon' => 'sign-in-alt', 'visible' => Yii::$app->user->isGuest],
                    ['label' => 'Категории', 'iconStyle' => 'far', 'url' => ['/admin/categories'], 'visible' => (!Yii::$app->user->isGuest)],
                    ['label' => 'Товары', 'iconStyle' => 'far', 'url' => ['/admin/products'], 'visible' => (!Yii::$app->user->isGuest)],
                    ['label' => 'Заказы', 'iconStyle' => 'far', 'url' => ['/admin/orders'], 'visible' => (!Yii::$app->user->isGuest)],
                    ['label' => 'Выход', 'iconClass' => 'nav-icon fas fa-sign-out-alt', 'url' => ['/admin/logout'], 'visible' => (!Yii::$app->user->isGuest)],
                   
                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>