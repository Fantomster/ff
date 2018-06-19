<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\DetailView;
use yii\helpers\Html;
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.client.integration.view_vsd', ['ru' => 'Просмотр ВСД']) ?></h4>
</div>
<div class="modal-body">
        <div class="box-header with-border">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-check"></i><?= Yii::t('message', 'frontend.client.integration.mercury.successful', ['ru' => 'Выполнено']) ?>
                            </h4>
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-exclamation-circle"></i><?= Yii::t('message', 'frontend.views.vendor.error', ['ru' => 'Ошибка']) ?>
                            </h4>
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>
                    <h4>Сведения о ВСД: </h4>
                    <?php echo DetailView::widget([
                        'model' => $document,
                        'attributes' => [
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => $document->statuses[$document->status],
                            ],
                            [
                                'label' => 'Номер',
                                'format' => 'raw',
                                'value' => $document->getNumber(),
                            ],
                            [
                                'attribute' => 'issueDate',
                                'format' => 'raw',
                                'value' => $document->issueDate,
                            ],
                            [
                                'attribute' => 'form',
                                'format' => 'raw',
                                'value' => $document->forms[$document->form],
                            ],
                            [
                                'attribute' => 'type',
                                'format' => 'raw',
                                'value' => $document->types[$document->type],
                            ],
                        ],
                    ]) ?>
                    <h4>Сведения об отправителе: </h4>
                    <?php echo DetailView::widget([
                        'model' => $document,
                        'attributes' => [
                            [
                                'label' => $document->consignor[0]['label'],
                                'value' => $document->consignor[0]['value']
                            ],
                            [
                                'label' => $document->consignor[1]['label'],
                                'value' => $document->consignor[1]['value']
                            ],
                        ],
                    ]) ?>
                    <h4>Сведения о получателе: </h4>
                    <?php echo DetailView::widget([
                        'model' => $document,
                        'attributes' => [
                            [
                                'label' => $document->consignee[0]['label'],
                                'value' => $document->consignee[0]['value']
                            ],
                            [
                                'label' => $document->consignee[1]['label'],
                                'value' => $document->consignee[1]['value']
                            ],
                        ],
                    ]) ?>
                    <h4>Информация о продукции: </h4>
                    <?php
                    $attributes = [];

                    foreach ($document->batch as $row)
                    {
                        if(isset($row['value']))
                            $attributes[] = $row;
                    }

                    echo DetailView::widget([
                        'model' => $document,
                        'attributes' => $attributes,
                    ]) ?>

                    <h4>Информация о транспорте: </h4>
                    <?php
                    $attributes = [
                            [
                            'label' => 'Тип',
                            'value' => $document->transportInfo['type']
                           ]
                    ];

                    if(isset($document->transportInfo)){
                        foreach ($document->transportInfo['numbers'] as $row)
                        {
                            if(!empty($row['number']))
                                $attributes[] = [
                                        'label' => $row['label'],
                                        'value' => $row['number'],
                            ];
                        }

                        echo DetailView::widget([
                            'model' => $document,
                            'attributes' => $attributes,
                        ]); }?>
                    <h4>Транспортная накладная: </h4>
                    <?php
                    $attributes = [
                        [
                            'label' => 'Номер',
                            'value' => $document->getWaybillNumber()
                        ],

                        [
                            'label' => 'Дата',
                            'value' => $document->waybillDate
                        ]
                    ];

                    echo DetailView::widget([
                        'model' => $document,
                        'attributes' => $attributes,
                    ]) ?>

                    <h4>Кто выписал ВСД: </h4>
                    <?php
                    echo DetailView::widget([
                        'model' => $document,
                        'attributes' => $document->confirmedBy
                    ]) ?>

                    <h4>Прочая информация: </h4>
                    <?php
                    $attributes = [];

                    if(isset($document->broker))
                        $attributes[] = $document->broker;

                    if(isset($document->purpose))
                    $attributes[] = $document->purpose;

                    if(isset($document->transportStorageType))
                        $attributes[] = [
                                'attribute' => 'transportStorageType',
                                'value' => $document->storage_types[$document->transportStorageType]
                        ];

                    if(isset($document->cargoExpertized))
                        $attributes[] = [
                            'attribute' => 'cargoExpertized',
                            'value' => ($document->cargoExpertized == 'true') ? 'Да' : 'Нет',
                        ];

                    if(isset($document->expertiseInfo))
                        $attributes[] = [
                            'attribute' => 'expertiseInfo',
                            'value' => (empty($document->expertiseInfo)) ? null : $document->expertiseInfo,
                        ];

                    if(isset($document->locationProsperity))
                        $attributes[] = [
                            'attribute' => 'locationProsperity',
                            'value' => $document->locationProsperity
                        ];

                    if(isset($document->specialMarks))
                        $attributes[] = [
                            'attribute' => 'specialMarks',
                            'value' => $document->specialMarks
                        ];

                    echo DetailView::widget([
                        'model' => $document,
                        'attributes' => $attributes,
                    ]) ?>
            </div>
        </div>
</div>
<div class="modal-footer">
    <?php if ($document->status == \frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest::DOC_STATUS_CONFIRMED) {
            echo Html::a(Yii::t('message', 'frontend.client.integration.done', ['ru' => 'Погасить']), ['done', 'uuid'=>$document->UUID], ['class' => 'btn btn-success']).' '.
                Html::a(Yii::t('message', 'frontend.client.integration.done_partial', ['ru' => 'Частичная приемка']), ['done-partial', 'uuid'=>$document->UUID], ['class' => 'btn btn-warning', 'data' => [
                    //'pjax'=>0,
                    'target' => '#ajax-load',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                ],]).' '.
                Html::a(Yii::t('message', 'frontend.client.integration.return_all', ['ru' => 'Возврат']), ['done-partial', 'uuid'=>$document->UUID, 'reject' => true], ['class' => 'btn btn-danger', 'data' => [
                    //'pjax'=>0,
                    'target' => '#ajax-load',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                ],]);
            } ?>
              <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close_four', ['ru'=>'Закрыть']) ?></a>
</div>
