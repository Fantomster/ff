<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\DetailView;
use yii\helpers\Html;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Просмотр ВСД
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr/default'],
            ],
            [
                'label' => 'Интеграция с системой ВЕТИС "Меркурий"',
                'url' => ['/clientintegr/merc/default'],
            ],
            'Просмотр ВСД',
        ],
    ])
    ?>
</section>

<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <p>
                        Состояние лицензии:
                        <?php echo '<strong>Активна</strong> ID: ' . $lic->code . ' (с ' . date("d-m-Y H:i:s", strtotime($lic->fd)) . ' по ' . date("d-m-Y H:i:s", strtotime($lic->td)) . ') '; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
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
                    ]) ?>

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
                    <div class="col-md-12">
                        <?= Html::a('Погасить', ['done', 'uuid'=>$document->UUID], ['class' => 'btn btn-success']).' '.
                        Html::a('Частичня приемка', ['#'], ['class' => 'btn btn-warning']).' '.
                        Html::a('Вернуть', ['#'], ['class' => 'btn btn-danger']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
