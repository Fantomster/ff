<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AgentRequest */

$this->title = Yii::t('app', "Заявка №") . $model->id;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Html::encode($this->title) ?>
    </h1>
</section>
<section class="content">
    <div class="row hidden-xs">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?=
                    Html::a('Delete', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this item?',
                            'method' => 'post',
                        ],
                    ])
                    ?>
                </div>
                <div class="box-body">
                    <?=
                    DetailView::widget([
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
                    ])
                    ?>

                    <div style="padding-top: 20px;">Приложения:
                        <?php foreach ($model->attachments as $attachment) { ?>
                            <a href="<?= $attachment->getUploadUrl("attachment") ?>"><?= $attachment->attachment ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>