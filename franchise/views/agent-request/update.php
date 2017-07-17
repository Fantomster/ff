<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AgentRequest */

$this->title = 'Update Agent Request: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Agent Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="agent-request-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('_form', [
        'model' => $model,
        'attachment' => $attachment,
    ])
    ?>

</div>
