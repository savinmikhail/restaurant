<?php

use yii\helpers\Url;
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Url::to(['/admin']) ?>" class="brand-link">
        <img src="<?= Url::to(['/upload/logo.png']) ?>" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Кинцуги</span>
    </a>


<!-- Sidebar -->
<div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <?php
        echo \hail812\adminlte\widgets\Menu::widget([
            'items' => [
                ['label' => 'Login', 'url' => ['admin/login'], 'icon' => 'sign-in-alt', 'visible' => Yii::$app->user->isGuest],
                ['label' => 'Настройки', 'iconStyle' => 'far', 'url' => ["/admin/settings"], 'visible' => (!Yii::$app->user->isGuest)],
                ['label' => 'Официанты', 'iconStyle' => 'far', 'url' => ["/admin/users"], 'visible' => (!Yii::$app->user->isGuest)],
                ['label' => 'Выход', 'iconClass' => 'nav-icon fas fa-sign-out-alt', 'url' => ['/admin/logout'], 'visible' => (!Yii::$app->user->isGuest)],
            ],
        ]);
        ?>
    </nav>
    <!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->
</aside>