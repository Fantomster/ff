<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\AgentRequest */

$this->title = "Заявка №" . $model->id;
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
                <div class="box-body">
                    <?=
                    DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'agent_id',
                            'target_email:email',
                            [
                                'attribute' => 'comment',
                                'label' => 'Комментарий'
                            ],
                            [
                                'attribute' => 'is_processed',
                                'label' => 'Обработано'
                            ],
                            'franchisee.signed',
                            'franchisee.legal_entity',
                            'created_at',
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
<hr>
<section class="content-header">
    <h2>
        <i class="fa fa-home"></i> Организации
    </h2>
</section>
<section class="content">
    <div class="row hidden-xs">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body">
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            'id',
                            'name',
                            'email',
                            'franchisee.signed',
                            'franchisee.legal_entity',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'buttons' => [
                                    'view' => function ($url, $organization) use ($model) {

                                        $customUrl = Yii::$app->getUrlManager()->createUrl(['agent-request/link', 'id' => $model->id, 'org_id' => $organization['id'], 'franchisee_id' => isset($model->franchisee->id) ? $model->franchisee->id : 1, 'agent_id' => $model->agent_id]);
                                        return \yii\helpers\Html::a('<div class="btn btn-sm btn-danger">Привязать к франчайзи агента</div>', $customUrl,
                                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);

                                    }
                                ],
                                'template' => '{view}',
                            ],
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>