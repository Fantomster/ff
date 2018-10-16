<?php

use yii\widgets\DetailView;
use api\common\models\merc\MercVsd;

?>
    <h4>Сведения о ВСД: </h4>
<?php echo DetailView::widget([
    'model' => $document,
    'attributes' => [
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => MercVsd::$statuses[$document->status],
        ],
        [
            'label' => 'Номер',
            'format' => 'raw',
            'value' => MercVsd::getNumber($document->issueSeries, $document->issueNumber),
        ],
        [
            'attribute' => 'issueDate',
            'format' => 'raw',
            'value' => $document->issueDate,
        ],
        [
            'attribute' => 'form',
            'format' => 'raw',
            'value' => MercVsd::$forms[$document->form],
        ],
        [
            'attribute' => 'type',
            'format' => 'raw',
            'value' => MercVsd::$types[$document->type],
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

foreach ($document->batch as $row) {
    if (isset($row['value']))
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

if (isset($document->transportInfo)) {
    foreach ($document->transportInfo['numbers'] as $row) {
        if (!empty($row['number']))
            $attributes[] = [
                'label' => $row['label'],
                'value' => $row['number'],
            ];
    }

    echo DetailView::widget([
        'model' => $document,
        'attributes' => $attributes,
    ]);
} ?>
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

if (isset($document->laboratory_research)) {
    $attributes[] = [
        'label' => 'Результат лабораторного исследования',
        'value' => implode(", ", $document->laboratory_research)
    ];
}

if (isset($document->broker)) {
    $attributes[] = $document->broker;
}

if (isset($document->purpose)) {
    $attributes[] = $document->purpose;
}

if (isset($document->transportStorageType)) {
    $attributes[] = [
        'attribute' => 'transportStorageType',
        'value' => MercVsd::$storage_types[$document->transportStorageType]
    ];
}

if (isset($document->cargoExpertized)) {
    $attributes[] = [
        'attribute' => 'cargoExpertized',
        'value' => ($document->cargoExpertized == 'true') ? 'Да' : 'Нет',
    ];
}

/*if(isset($document->expertiseInfo))
    $attributes[] = [
        'attribute' => 'expertiseInfo',
        'value' => (empty($document->expertiseInfo)) ? null : $document->expertiseInfo,
    ];*/

if (isset($document->locationProsperity)) {
    $attributes[] = [
        'attribute' => 'locationProsperity',
        'value' => $document->locationProsperity
    ];
}

if (isset($document->specialMarks)) {
    $attributes[] = [
        'attribute' => 'specialMarks',
        'value' => $document->specialMarks
    ];
}

echo DetailView::widget([
    'model' => $document,
    'attributes' => $attributes,
]) ?>