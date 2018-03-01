<input type="hidden" id="invoice_id" value="<?=$model->id?>">
<div class="panel panel-info">
    <div class="panel-heading">
        Накладная

        <a class="btn btn-xs btn-primary" href="invoice/download?id=<?=$model->id?>" target="_blank">
            <i class="fa fa-save"></i>
        </a>

        <?=\yii\helpers\Html::button(
                'Создать новый заказ для поставщика ' . $vendor->name,
                [
                    'data' => [
                        'invoice_id' => $model->id,
                        'vendor_id' => $vendor->id
                    ],
                    'class' => 'btn btn-success btn-xs pull-right create-order'
                ]
            )
        ?>

    </div>
    <div class="panel-body">
        <span><b>Номер</b>: <?= $model->number ?></span>
        <?php if (!empty($model->date) && $model->date != '0000-00-00 00:00:00'): ?>
            <span style="margin-left: 30px"><b>Дата</b>: <?= date('d.m.Y', strtotime($model->date)) ?></span>
        <?php endif; ?>
    </div>
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
            'percent_nds',
            ['attribute' => 'price_nds', 'pageSummary' => true],
            ['attribute' => 'totalPrice', 'pageSummary' => true],
            'ed',
            'quantity'
        ]
    ]); ?>
</div>
