<?php

use yii\helpers\Html;

$this->title = 'Административная панель';
?>
<div class="admin-welcome">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= $welcomeMessage ?></p>
</div>