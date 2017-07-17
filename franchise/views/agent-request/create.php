<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AgentRequest */

$this->title = 'Create Agent Request';
$this->params['breadcrumbs'][] = ['label' => 'Agent Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agent-request-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('_form', [
        'model' => $model,
        'attachment' => $attachment,
    ])
    ?>

</div>
