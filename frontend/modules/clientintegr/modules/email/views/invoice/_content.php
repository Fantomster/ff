<?php
echo \yii\helpers\Html::input('hidden', 'invoice_id', $model->id);
?>

<?= \kartik\grid\GridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => $model->content]),
    'striped' => true,
    'condensed' => true,
    'summary' => false,
    'responsive' => true,
    'showPageSummary' => true,
    'columns' => [
        [
            'attribute' => 'row_number',
            'header' => '№ п/п',
        ],
        'article',
        'title',
        'quantity',
        'ed',
        ['attribute' => 'price_without_nds', 'pageSummary' => true],
        'percent_nds',
        ['attribute' => 'price_without_nds', 'pageSummary' => true],

      //  ['attribute' => 'totalPrice', 'pageSummary' => true],

        ['attribute' => 'price_nds', 'pageSummary' => true],


    ]
]); ?>
