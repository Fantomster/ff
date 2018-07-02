<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'Редактирование пользователя: ' . $user->profile->full_name;
$this->params['breadcrumbs'][] = ['label' => $_SESSION["clients_name"], 'url' => [$_SESSION["clients"]]];
$this->params['breadcrumbs'][] = ['label' => $user->id, 'url' => ['view', 'id' => $user->id]];
$this->params['breadcrumbs'][] = 'Редактирование пользователя';
?>
<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', compact('user', 'profile')) ?>

</div>
