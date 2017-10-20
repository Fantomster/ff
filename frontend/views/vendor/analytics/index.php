<?php

use yii\widgets\Breadcrumbs;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use dosamigos\chartjs\ChartJs;

$this->title = 'Аналитика';
$this->registerCss('
.box-analytics {border:1px solid #eee}.input-group.input-daterange .input-group-addon {
    border-left: 0px;
}
tfoot tr{border-top:2px solid #ccc}
.info-box-content{color:#84bf76;-webkit-box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);
-moz-box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);
box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);}
.order-history .info-box {
     box-shadow: none; 
}
.info-box {
     box-shadow: none;
     border:1px solid #eee;
}
.info-box-text {
    color: #555;
}
');
?>
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> Аналитика
        <small>Вся аналитика в одном месте</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Аналитика'
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info">

        <!-- /.box-header -->
        <div class="box-body order-history">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-total-price">
                    <div class="info-box-content">
                        <span class="info-box-number"><?= $headerStats["ordersCount"]; ?></span>
                        <span class="info-box-text">Всего заказов</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-total-price">
                    <div class="info-box-content">
                        <span class="info-box-number"><?= $headerStats["goodsCount"]; ?></span>
                        <span class="info-box-text">Всего товаров</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-total-price">
                    <div class="info-box-content">
                        <span class="info-box-number"><?= $headerStats["clientsCount"]; ?></span>
                        <span class="info-box-text">Всего клиентов</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-total-price">
                    <div class="info-box-content">
                        <span class="info-box-number"><?= $headerStats["totalTurnover"]; ?></span>
                        <span class="info-box-text">Оборот</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <?= Html::label('Сотрудник', null, ['class' => 'label', 'style' => 'color:#555']) ?>
                <?= Html::dropDownList('filter_employee', null, $filter_get_employee, ['prompt' => 'Все сотрудники', 'class' => 'form-control', 'id' => 'filter_employee'])
                ?>        
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <?= Html::label('Статус заказа', null, ['class' => 'label', 'style' => 'color:#555']) ?>
                <?=
                Html::dropDownList('filter_status', null, [
                    '1' => 'Ожидание от поставщика',
                    '2' => 'Ожидание от заказчика',
                    '3' => 'Выполняется',
                    '4' => 'Завершен',
                    '5' => 'Отменен заказчиком',
                    '6' => 'Отменен поставщиком',
                        ], ['prompt' => 'Все', 'class' => 'form-control', 'id' => 'filter_status',
                    'options' => [\Yii::$app->request->get('filter_status') => ["Selected" => true]]])
                ?>         
            </div>
            <div class="col-lg-5 col-md-5 col-sm-6"> 
                <?php
                $layout = <<< HTML
    {input1}
    {separator}
    {input2}
HTML;
                ?>

                <?= Html::label('Начальная дата / Конечная дата', null, ['class' => 'label', 'style' => 'color:#555']) ?>

                <?=
                DatePicker::widget([
                    'name' => 'filter_from_date',
                    'id' => 'filter-date',
                    'value' => \Yii::$app->request->get('filter_from_date') ? \Yii::$app->request->get('filter_from_date') : $filter_from_date,
                    'type' => DatePicker::TYPE_RANGE,
                    'name2' => 'filter_to_date',
                    'value2' => \Yii::$app->request->get('filter_to_date') ? \Yii::$app->request->get('filter_to_date') : $filter_to_date,
                    'separator' => '-',
                    'layout' => $layout,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd-mm-yyyy',
                        'todayHighlight' => true,
                        'endDate' => "0d",
                    ],
                    'removeButton' => false,
                ]);
                ?>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <?= Html::label('Клиент', null, ['class' => 'label', 'style' => 'color:#555']) ?>
                <?= Html::dropDownList('filter_client', null, $filter_restaurant, ['prompt' => 'Все', 'class' => 'form-control', 'id' => 'filter_client', 'options' => [\Yii::$app->request->get('filter_client') => ["Selected" => true]]])
                ?>        
            </div>
            <div class="col-lg-1 col-md-1 col-sm-2">
                <?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
                <?= Html::button('<i class="fa fa-times" aria-hidden="true"></i>', ['id' => 'reset', 'class' => 'form-control clear_filters btn btn-outline-danger teaser']) ?>        
            </div>
        </div>
        <!-- /.box-body -->
    </div>

    <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'analytics-list',]); ?>
    <div class="row">
        <div class="col-md-6">
            <!-- AREA CHART -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Объем продаж</h3>

                    <div class="box-tools pull-right">
                    </div>
                </div>
                <div class="box-body" style="display: block;">
                    <div class="chart" style="position:relative;height:100%;width:100%;min-height: 286px;">
                        <?=
                        ChartJs::widget([
                            'type' => 'line',
                            'options' => [
                                'maintainAspectRatio' => false,
                                'responsive' => true,
                                'height' => '282px',
                            ],
                            'data' => [
                                'labels' => $arr_create_at,
                                'datasets' => [
                                    [
                                        'label' => "Объем продаж",
                                        'fillColor' => "rgba(0,0,0,.05)",
                                        'borderColor' => "#84bf76",
                                        'data' => $arr_price,
                                    ]
                                ],
                            ],
                        ]);
                        ?>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col (LEFT) -->
        <div class="col-md-6">
            <!-- pie CHART -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Продажи по клиентам</h3>

                    <div class="box-tools pull-right">
                    </div>
                </div>
                <div class="box-body" style="display: block;">
                    <div style="position:relative;height:282px;width:282px;min-height: 286px;margin: auto;">
                        <?=
                        ChartJs::widget([
                            'type' => 'pie',
                            'options' => [
                                'height' => 282,
                                'width' => 282,
                            ],
                            'data' => [
                                'labels' => $arr_clients_labels,
                                'datasets' => [
                                    [
                                        'data' => $arr_clients_price,
                                        'backgroundColor' => $arr_clients_colors,
                                        'hoverBackgroundColor' => $arr_clients_colors,
                                    ]
                                ],
                            ],
                        ]);
                        ?>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <div class="col-md-6">
            <!-- pie CHART -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Продажи по товарам</h3>

                    <div class="box-tools pull-right">
                    </div>
                </div>
                <div class="box-body" style="display: block;">
                    <div>
                        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'product-analytic-list',]); ?>
                        <?php
                        $columns = [
                            [
                                //'attribute' => 'product_id',
                                'label' => 'Товар',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::decode(Html::decode(\common\models\CatalogBaseGoods::find()->where(['id' => $data['product_id']])->one()->product));
                                },
                                'contentOptions' => ['style' => 'vertical-align:middle;'],
                                'footer' => 'ИТОГО: ',
                            ],
                            [
                                //'attribute' => 'price',
                                'format' => 'raw',
                                'label' => 'Итого',
                                'value' => function($data) {
                                    return (float) $data['price'] . "<i class=\"fa fa-fw fa-rub\"></i>";
                                },
                                'contentOptions' => ['style' => 'vertical-align:middle;font-weight:bold'],
                                'footer' => $total_price . "<i class=\"fa fa-fw fa-rub\"></i>",
                            ]
                        ];
                        ?>
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterPosition' => false,
                            'columns' => $columns,
                            'tableOptions' => ['class' => 'table no-margin'],
                            'options' => ['class' => 'table-responsive'],
                            'bordered' => false,
                            'striped' => false,
                            'resizableColumns' => false,
                            'condensed' => false,
                            'responsive' => false,
                            'summary' => false,
                            'hover' => true,
                            'showFooter' => TRUE,
                            'footerRowOptions' => ['class' => 'text-success', 'style' => 'font-weight:bold;text-decoration: underline;'],
                            'columns' => $columns,
                        ]);
                        ?> 
                        <?php Pjax::end(); ?>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>

    </div>
    <?php Pjax::end(); ?>
</section>
<?php
$filter_clear_from_date = date("d-m-Y", strtotime(" -2 months"));
$filter_clear_to_date = date("d-m-Y");

$analyticsUrl = Url::to(['vendor/analytics']);

$customJs = <<< JS
$("#filter_status,#filter_employee,#filter-date,#filter-date-2,#filter_client").on("change", function () {
$("#filter_status,#filter_employee,#filter-date,#filter-date-2,#filter_client").attr('disabled','disabled')
var filter_status = $("#filter_status").val();
var filter_from_date =  $("#filter-date").val();
var filter_to_date =  $("#filter-date-2").val();
var filter_client =  $("#filter_client").val();
var filter_employee =  $("#filter_employee").val();

    $.pjax({
     type: 'GET',
     push: false,
     timeout: 10000,
     url: "$analyticsUrl",
     container: "#analytics-list",
     data: {
         filter_status: filter_status,
         filter_from_date: filter_from_date,
         filter_to_date: filter_to_date,
         filter_client: filter_client,
         filter_employee: filter_employee,
           }
   }).done(function() { $("#filter_status,#filter-date,#filter-date-2,#filter_client,#filter_employee").removeAttr('disabled') });
});
$("#reset").on("click", function () {
    $("#filter_status").val('');
    $("#filter-date").val('$filter_clear_from_date');
    $("#filter-date-2").val('$filter_clear_to_date');
    $("#filter_client").val('');     
    $("#filter_employee").val('');        
    $.pjax({
     type: 'GET',
     push: false,
     timeout: 10000,
     url: "$analyticsUrl",
     container: "#analytics-list",
     data: {
         filter_status: '',
         filter_from_date: '$filter_clear_from_date',
         filter_to_date: '$filter_clear_to_date',
         filter_client: '',
         filter_supplier: filter_supplier,
         filter_employee: filter_employee,
           }
   }).done(function() { $("#filter_status,#filter-date,#filter-date-2,#filter_client,#filter_employee").removeAttr('disabled') });
})
$.pjax({
     type: 'GET',
     push: false,
     timeout: 10000,
     url: "$analyticsUrl",
     container: "#product-analytic-list",
     data: {
         filter_status: '',
         filter_from_date: '$filter_clear_from_date',
         filter_to_date: '$filter_clear_to_date',
         filter_client: '',
         filter_employee: '',
           }
   })
JS;
$this->registerJs($customJs, View::POS_READY);

