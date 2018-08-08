<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'Редактирование пользователя: ' . $user->profile->full_name;
$clients = !empty(Yii::$app->session->get('clients')) ? Yii::$app->session->get('clients') : 'index';
$clientsName = !empty(Yii::$app->session->get('clients_name')) ? Yii::$app->session->get('clients_name') : 'Пользователи';
$this->params['breadcrumbs'][] = ['label' => $clientsName, 'url' => [$clients]];
$this->params['breadcrumbs'][] = ['label' => $user->id, 'url' => ['view', 'id' => $user->id]];
$this->params['breadcrumbs'][] = 'Редактирование пользователя';
?>
<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', compact('user', 'profile', 'dropDown', 'selected', 'currentUser')) ?>

</div>
