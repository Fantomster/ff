<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AgentRequest */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Agent Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agent-request-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'agent_id',
            'target_email:email',
            'comment',
            'is_processed',
            'created_at',
            'updated_at',
        ],
    ]) ?>
    
    Приложения:
    <?php foreach($model->attachments as $attachment) { ?>
    <a href="<?= $attachment->getUploadUrl("attachment") ?>"><?= $attachment->attachment ?></a>
    <?php } ?>
</div>
