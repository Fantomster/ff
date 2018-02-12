<?php

use yii\widgets\Breadcrumbs;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use dosamigos\chartjs\ChartJs;

$this->title = Yii::t('message', 'frontend.views.vendor.anal_two', ['ru' => 'Аналитика']);
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
.alUl{
    list-style: none;
    margin-left: 10px;
}
.alColor{
    display: block;
    float: left;
    width: 30px;
    margin-top: 5px;
    height: 12px;
}
.alLabel{
    display: block;
    margin-left: 40px;
}
.alLi{
    cursor: pointer;
}
.alStrikethrough .alLabel{
    text-decoration: line-through;
}
');
?>
    <section class="content-header">
        <h1>
            <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.vendor.anal_three', ['ru' => 'Аналитика']) ?>
            <small><?= Yii::t('message', 'frontend.views.vendor.all_anal', ['ru' => 'Вся аналитика в одном месте']) ?></small>
        </h1>
        <?php
        $json_clients_labels = json_encode($arr_clients_labels);
        $json_clients_total_price = json_encode($arr_clients_price);
        $json_clients_colors = json_encode($arr_clients_colors);
        ?>
        <?=
        Breadcrumbs::widget([
            'options' => [
                'class' => 'breadcrumb',
            ],
            'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
            'links' => [
                Yii::t('message', 'frontend.views.vendor.anal_four', ['ru' => 'Аналитика'])
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
                            <span class="info-box-text"><?= Yii::t('message', 'frontend.views.vendor.total_orders', ['ru' => 'Всего заказов']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-total-price">
                        <div class="info-box-content">
                            <span class="info-box-number"><?= $headerStats["goodsCount"]; ?></span>
                            <span class="info-box-text"><?= Yii::t('message', 'frontend.views.vendor.total_goods', ['ru' => 'Всего товаров']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-total-price">
                        <div class="info-box-content">
                            <span class="info-box-number"><?= $headerStats["clientsCount"]; ?></span>
                            <span class="info-box-text"><?= Yii::t('message', 'frontend.views.vendor.total_clients', ['ru' => 'Всего клиентов']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-total-price">
                        <div class="info-box-content">
                            <span class="info-box-number"><?= $headerStats["totalTurnover"]; ?></span>
                            <span class="info-box-text"><?= Yii::t('message', 'frontend.views.vendor.turnover', ['ru' => 'Оборот']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?= Html::label(Yii::t('message', 'frontend.views.vendor.empl_two', ['ru' => 'Сотрудник']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <?= Html::dropDownList('filter_employee', null, $filter_get_employee, ['prompt' => Yii::t('message', 'frontend.views.vendor.all_empl', ['ru' => 'Все сотрудники']), 'class' => 'form-control', 'id' => 'filter_employee'])
                    ?>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?= Html::label(Yii::t('message', 'frontend.views.vendor.order_status', ['ru' => 'Статус заказа']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <?=
                    Html::dropDownList('filter_status', null, [
                        '1' => Yii::t('message', 'frontend.views.vendor.vendor_wait', ['ru' => 'Ожидание от поставщика']),
                        '2' => Yii::t('message', 'frontend.views.vendor.customer_wait', ['ru' => 'Ожидание от заказчика']),
                        '3' => Yii::t('message', 'frontend.views.vendor.in_process', ['ru' => 'Выполняется']),
                        '4' => Yii::t('message', 'frontend.views.vendor.complete', ['ru' => 'Завершен']),
                        '5' => Yii::t('message', 'frontend.views.vendor.canc_by_cust', ['ru' => 'Отменен заказчиком']),
                        '6' => Yii::t('message', 'frontend.views.vendor.canc_by_vendor', ['ru' => 'Отменен поставщиком']),
                    ], ['prompt' => Yii::t('message', 'frontend.views.vendor.all_four', ['ru' => 'Все']), 'class' => 'form-control', 'id' => 'filter_status',
                        'options' => [\Yii::$app->request->get('filter_status') => ["Selected" => true]]])
                    ?>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <?php
                    $layout = <<< HTML
    {input1}
    {separator}
    {input2}
HTML;
                    ?>

                    <?= Html::label(Yii::t('message', 'frontend.views.vendor.begin_end', ['ru' => 'Начальная дата / Конечная дата']), null, ['class' => 'label', 'style' => 'color:#555']) ?>

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

                <div class="col-lg-2 col-md-2 col-sm-6">
                    <?= Html::label(Yii::t('message', 'frontend.views.vendor.client', ['ru' => 'Клиент']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <?= Html::dropDownList('filter_client', null, $filter_restaurant, ['prompt' => Yii::t('message', 'frontend.views.vendor.all_five', ['ru' => 'Все']), 'class' => 'form-control', 'id' => 'filter_client', 'options' => [\Yii::$app->request->get('filter_client') => ["Selected" => true]]])
                    ?>
                </div>
                <div class="col-lg-1 col-md-1 col-sm-6">
                    <?= Html::label(Yii::t('message', 'frontend.views.client.anal.currency', ['ru'=>'Валюта']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <?=
                    Html::dropDownList('filter_currency', null, $currencyList, ['class' => 'form-control', 'id' => 'filter_currency'])
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
                        <h3 class="box-title"><?= Yii::t('message', 'frontend.views.vendor.sells_value', ['ru' => 'Объем продаж']) ?></h3>

                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <div class="box-body" style="display: block;">
                        <div class="chart" style="position:relative;height:100%;width:100%;min-height: 586px;">
                            <?=
                            ChartJs::widget([
                                'type' => 'line',
                                'options' => [
                                    'maintainAspectRatio' => false,
                                    'responsive' => true,
                                    'height' => '582px',
                                ],
                                'data' => [
                                    'labels' => $arr_create_at,
                                    'datasets' => [
                                        [
                                            'label' => Yii::t('message', 'frontend.views.vendor.sell_value_three', ['ru' => "Объем продаж"]),
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
                        <h3 class="box-title"><?= Yii::t('message', 'frontend.views.vendor.sells_by_clients', ['ru' => 'Продажи по клиентам']) ?></h3>

                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <div class="box-body" style="display: block;">
                        <div class="col-lg-6 col-md-12 col-sm-12" style="max-height: 352px; overflow-y: scroll">
                            <script>
                                var json_clients_labels_source = <?= $json_clients_labels ?>;
                                var json_clients_total_price_source = <?= $json_clients_total_price ?>;
                                var json_clients_colors_source = <?= $json_clients_colors ?>;
                            </script>
                            <ul class="alUl">
                                <?php foreach ($arr_clients_colors as $id => $color): ?>
                                    <li class="alLi" color-id="<?= $id ?>"><span class="alColor"
                                                                                 style="background-color: <?= $color ?>"></span><span
                                                class="alLabel"><?= $arr_clients_labels[$id] ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div id="alWrapper" style="position:relative; width:80%; min-height: 286px; margin: 1px;">
                                <?=
                                ChartJs::widget([
                                    'type' => 'pie',
                                    'clientOptions' => [
                                        'legend' => false,
                                    ],
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
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <div class="col-md-6">
                <!-- pie CHART -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('message', 'frontend.views.vendor.sells_by_goods', ['ru' => 'Продажи по товарам']) ?></h3>

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
                                    'label' => Yii::t('message', 'frontend.views.vendor.good', ['ru' => 'Товар']),
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return Html::decode(Html::decode(\common\models\CatalogBaseGoods::find()->where(['id' => $data['product_id']])->one()->product));
                                    },
                                    'contentOptions' => ['style' => 'vertical-align:middle;'],
                                    'footer' => Yii::t('message', 'frontend.views.vendor.total', ['ru' => 'ИТОГО: ']),
                                ],
                                [
                                    //'attribute' => 'price',
                                    'format' => 'raw',
                                    'label' => Yii::t('message', 'frontend.views.vendor.total_two', ['ru' => 'Итого']),
                                    'value' => function ($data) use (&$total_price) {
                                        $total_price = $total_price . $data['iso_code'];
                                        return (float)round($data['price'], 2) . ' ' . $data['iso_code'];
                                    },
                                    'contentOptions' => ['style' => 'vertical-align:middle;font-weight:bold'],
                                    'footer' => $total_price
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

$(document).on("click", ".alLi", function() {
      $('#w1').remove();
      $('#alWrapper').append('<canvas id="w1" width="350" height="350" style="display: block; width: 350px; height: 350px;" class="chartjs-render-monitor"></canvas>');
      var pieChart = $('#w1');
      var id = $(this).attr("color-id");
      var json_clients_colors = $.extend([], json_clients_colors_source);
      var json_clients_total_price = $.extend([], json_clients_total_price_source);
      var json_clients_labels = json_clients_labels_source;
      $(".alLi").each(function() {
          var color_id = $(this).attr("color-id");
          if($(this).hasClass("alStrikethrough")){
              if(color_id == id){
                  $(this).removeClass("alStrikethrough");
              }else{
                json_clients_colors[color_id] = null;
                 json_clients_total_price[color_id] = 0;
              }
          }else{
              if(color_id == id){
                  $(this).addClass("alStrikethrough");
                  json_clients_colors[color_id] = null;
                 json_clients_total_price[color_id] = 0;
              }
          }
      });
      var newChart = new Chart(pieChart, {
        type: 'pie',
        data: {
                  labels : json_clients_labels,
                  datasets : [
                                {
                                  data : json_clients_total_price,
                                  backgroundColor : json_clients_colors,
                                  hoverBackgroundColor : json_clients_colors,
                                },
                      ]      
              },           
        options: {
            legend : false,
            height : 382,
            width : 382,

        },
    });
});

$("#filter_status,#filter_employee,#filter-date,#filter-date-2,#filter_client,#filter_currency").on("change", function () {
$("#filter_status,#filter_employee,#filter-date,#filter-date-2,#filter_client,#filter_currency").attr('disabled','disabled')
var filter_status = $("#filter_status").val();
var filter_from_date =  $("#filter-date").val();
var filter_to_date =  $("#filter-date-2").val();
var filter_client =  $("#filter_client").val();
var filter_employee =  $("#filter_employee").val();
var filter_currency =  $("#filter_currency").val();

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
         filter_currency: filter_currency,
           }
   }).done(function() { $("#filter_status,#filter-date,#filter-date-2,#filter_client,#filter_employee,#filter_currency").removeAttr('disabled') });
});

$("#reset").on("click", function () {
    $("#filter_status").val('');
    $("#filter-date").val('$filter_clear_from_date');
    $("#filter-date-2").val('$filter_clear_to_date');
    $("#filter_client").val('');     
    $("#filter_employee").val('');        
    $("#filter_currency").val('1');   
    
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
         filter_employee: '',
         filter_currency: 1,
     }
   }).done(function() { 
       $("#filter_status,#filter-date,#filter-date-2,#filter_client,#filter_employee,#filter_currency").removeAttr('disabled'); 
   });
});
JS;
$this->registerJs($customJs, View::POS_READY);

