<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use branchonline\lightbox\Lightbox;

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
                                'attribute' => 'profile.full_name',
                                'label' => 'ФИО агента'
                            ],
                            [
                                'attribute' => 'agent.email',
                                'label' => 'Email агента'
                            ],
                            [
                                'attribute' => 'profile.phone',
                                'label' => 'Телефон агента'
                            ],
                            [
                                'attribute' => 'franchisee.legal_entity',
                                'label' => 'Название франчайзи агента'
                            ],
                            [
                                'attribute' => 'franchisee.signed',
                                'label' => 'Подписант франчайзи'
                            ],
                            'created_at',
                        ],
                    ])
                    ?>

                    <div style="padding-top: 20px;">Приложения:
                        <?php foreach ($model->attachments as $attachment) { ?>
                            <?php
                            echo Lightbox::widget([
                                'files' => [
                                    [
                                        'thumbOptions' => [
                                            'width' => 150
                                        ],
                                        'thumb' => $attachment->getUploadUrl("attachment"),
                                        'original' => $attachment->getUploadUrl("attachment"),
                                        'title' => $attachment->attachment,
                                    ],
                                ]
                            ]);
                            ?>
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
        <i class="fa fa-home"></i> Сотрудники организаций
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
                            [
                                'format' => 'raw',
                                'attribute' => 'org_name',
                                'value' => function ($data) {
                                    return Html::a($data['organization']['name'], ['organization/view', 'id' => $data['organization_id']]);
                                },
                                'label' => 'Название организации',
                            ],
                            'email',
                            'organization.franchisee.signed',
                            'organization.franchisee.legal_entity',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'buttons' => [
                                    'view' => function ($url, $user) use ($model) {
                                        if (isset($user->organization['id'])) {
                                            $customUrl = Yii::$app->getUrlManager()->createUrl(['agent-request/link', 'id' => $model->id, 'org_id' => $user->organization['id'], 'franchisee_id' => isset($model->franchisee->id) ? $model->franchisee->id : 1, 'agent_id' => $model->agent_id]);
                                            return \yii\helpers\Html::a('<div class="btn btn-sm btn-danger">Привязать к франчайзи агента</div>', $customUrl,
                                                ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                                        }
                                        return 'Пользователь не привязан к организации';
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