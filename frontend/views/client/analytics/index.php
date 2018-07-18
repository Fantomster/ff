<?php

use yii\widgets\Breadcrumbs;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use dosamigos\chartjs\ChartJs;
use kartik\form\ActiveForm;

$this->title = Yii::t('message', 'frontend.views.client.anal.anal', ['ru' => 'Аналитика']);
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
            <i class="fa fa-signal"></i> <?= Yii::t('message', 'frontend.views.client.anal.anal_two.', ['ru' => 'Аналитика']) ?>
            <small><?= Yii::t('message', 'frontend.views.client.anal.anal_three', ['ru' => 'Аналитика в одном месте']) ?></small>
        </h1>
        <?=
        Breadcrumbs::widget([
            'options' => [
                'class' => 'breadcrumb',
            ],
            'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
            'links' => [
                Yii::t('message', 'frontend.views.client.anal.anal_four', ['ru' => 'Аналитика'])
            ],
        ])
        ?>
        <?php
        $json_vendors_labels = json_encode($vendors_labels);
        $json_vendors_total_price = json_encode($vendors_total_price);
        $json_vendors_colors = json_encode($vendors_colors);
        ?>
    </section>
    <section class="content">
        <div class="box box-info">
            <!-- /.box-header -->
            <div class="box-body order-history">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-total-price">
                        <div class="info-box-content">
                            <span class="info-box-number"><?= $header_info_zakaz; ?></span>
                            <span class="info-box-text"><?= Yii::t('message', 'frontend.views.client.anal.total', ['ru' => 'Всего заказов']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-total-price">
                        <div class="info-box-content">
                            <span class="info-box-number"><?= $header_info_purchases; ?></span>
                            <span class="info-box-text"><?= Yii::t('message', 'frontend.views.client.anal.total_buy', ['ru' => 'Всего Закупок']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-total-price">
                        <div class="info-box-content">
                            <span class="info-box-number"><?= $header_info_suppliers ?></span>
                            <span class="info-box-text"><?= Yii::t('message', 'frontend.views.client.anal.total_vendors', ['ru' => 'Всего поставщиков']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-total-price">
                        <div class="info-box-content">
                            <span class="info-box-number"><?= $header_info_items; ?></span>
                            <span class="info-box-text"><?= Yii::t('message', 'frontend.views.client.anal.positions', ['ru' => 'Позиций']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?= Html::label(Yii::t('message', 'frontend.views.client.anal.vendor', ['ru' => 'Поставщик']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <?= Html::dropDownList('filter_supplier', null, $filter_get_supplier, ['prompt' => Yii::t('message', 'frontend.views.client.anal.all', ['ru' => 'Все поставщики']), 'class' => 'form-control', 'id' => 'filter_supplier'])
                    ?>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?= Html::label(Yii::t('message', 'frontend.views.client.anal.employee', ['ru' => 'Сотрудник']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <?= Html::dropDownList('filter_employee', null, $filter_get_employee, ['prompt' => Yii::t('message', 'frontend.views.client.anal.all_employees', ['ru' => 'Все сотрудники']), 'class' => 'form-control', 'id' => 'filter_employee'])
                    ?>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?= Html::label(Yii::t('message', 'frontend.views.client.anal.status', ['ru' => 'Статус заказа']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <?=
                    Html::dropDownList('filter_status', null, [
                        '1' => Yii::t('message', 'frontend.views.client.anal.waiting', ['ru' => 'Ожидание от поставщика']),
                        '2' => Yii::t('message', 'frontend.views.client.anal.client_waiting', ['ru' => 'Ожидание от заказчика']),
                        '3' => Yii::t('message', 'frontend.views.client.anal.in_process', ['ru' => 'Выполняется']),
                        '4' => Yii::t('message', 'frontend.views.client.anal.ready', ['ru' => 'Завершен']),
                        '5' => Yii::t('message', 'frontend.views.client.anal.client_cancel', ['ru' => 'Отменен заказчиком']),
                        '6' => Yii::t('message', 'frontend.views.client.anal.vendor_cancel', ['ru' => 'Отменен поставщиком']),
                    ], ['prompt' => Yii::t('message', 'frontend.views.client.anal.all_two', ['ru' => 'Все']), 'class' => 'form-control', 'id' => 'filter_status'])
                    ?>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <?php
                    $layout = <<< HTML
                {input1}
                {separator}
                {input2}
HTML;
                    ?>
                    <?= Html::label(Yii::t('message', 'frontend.views.client.anal.date', ['ru' => 'Начальная дата / Конечная дата']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <?=
                    DatePicker::widget([
                        'name' => 'filter_from_date',
                        'id' => 'filter-date',
                        'value' => $filter_from_date,
                        'type' => DatePicker::TYPE_RANGE,
                        'name2' => 'filter_to_date',
                        'value2' => $filter_to_date,
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
                <div class="col-lg-1 col-md-1 col-sm-6" id="alCurrencies">
                    <?php if (count($currencyList) > 0): ?>
                        <?= Html::label(Yii::t('message', 'frontend.views.client.anal.currency', ['ru' => 'Валюта']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                        <?=
                        Html::dropDownList('filter_currency', null, $currencyList, ['class' => 'form-control', 'id' => 'filter_currency'])
                        ?>
                    <?php endif; ?>
                </div>
                <div class="col-lg-1 col-md-1 col-sm-2">
                    <?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
                    <?= Html::button('<i class="fa fa-times" aria-hidden="true"></i>', ['id' => 'reset', 'class' => 'form-control clear_filters btn btn-outline-danger teaser']) ?>
                </div>
            </div>
        </div>
        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'analytics-list',]); ?>
        <div class="row">
            <div class="col-md-6">
                <!-- AREA CHART -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('message', 'frontend.views.client.anal.value', ['ru' => 'Объем заказов']) ?></h3>

                        <div class="box-tools pull-right">

                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display: block;">
                        <div class="chart" style="position:relative;height:286px;width:100%;min-height: 286px;">
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
                                            'label' => Yii::t('message', 'frontend.views.client.anal.value_orders', ['ru' => "Объем заказов"]),
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
            <div class="col-md-6">
                <!-- AREA CHART -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('message', 'frontend.views.client.anal.orders', ['ru' => 'Заказы по поставщикам']) ?></h3>

                        <div class="box-tools pull-right">

                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display: block;">
                        <div class="chart" style="position:relative;height:286px;width:100%;min-height: 286px;">
                            <?=
                            ChartJs::widget([
                                'type' => 'bar',
                                'options' => [
                                    'maintainAspectRatio' => false,
                                    'responsive' => true,
                                    'height' => '282px',
                                ],
                                'data' => [
                                    'labels' => $chart_bar_label,
                                    'datasets' => [
                                        [
                                            'label' => Yii::t('message', 'frontend.views.client.anal.orders_vendors', ['ru' => "Заказы по поставщикам"]),
                                            'data' => $chart_bar_value,
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
                <!-- AREA CHART -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('message', 'frontend.views.client.anal.vendor_value', ['ru' => 'Объем по поставщикам']) ?></h3>

                        <div class="box-tools pull-right">

                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display: block;">
                        <script>
                            var json_vendors_labels_source = <?= $json_vendors_labels ?>;
                            var json_vendors_total_price_source = <?= $json_vendors_total_price ?>;
                            var json_vendors_colors_source = <?= $json_vendors_colors ?>;
                        </script>
                        <div class="col-lg-6 col-md-12 col-sm-12" style="max-height: 352px; overflow-y: scroll">
                            <ul class="alUl">
                                <?php foreach ($vendors_colors as $id => $color): ?>
                                    <li class="alLi" color-id="<?= $id ?>"><span class="alColor"
                                                                                 style="background-color: <?= $color ?>"></span><span
                                                class="alLabel"><?= $vendors_labels[$id] ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div id="alWrapper" style="position:relative;width:80%; min-height: 336px; margin: auto;">
                                <?=
                                ChartJs::widget([
                                    'type' => 'pie',
                                    'clientOptions' => [
                                        'legend' => false
                                    ],
                                    'options' => [
                                        'height' => 282,
                                        'width' => 282,
                                    ],
                                    'data' => [
                                        'labels' => $vendors_labels,
                                        'datasets' => [
                                            [
                                                'data' => $vendors_total_price,
                                                'backgroundColor' => $vendors_colors,
                                                'hoverBackgroundColor' => $vendors_colors,
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
                <!-- AREA CHART -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('message', 'frontend.views.client.anal.stat', ['ru' => 'Статистика по товарам']) ?></h3>

                        <div class="box-tools pull-right">

                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display: block;">
                        <?php //Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'product-analytic-list',]); ?>
                        <?php
                        $columns = [
                            [
                                'attribute' => 'product_id',
                                'label' => Yii::t('message', 'frontend.views.client.anal.good', ['ru' => 'Товар']),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::decode(Html::decode(\common\models\CatalogBaseGoods::find()->where(['id' => $data['product_id']])->one()->product));
                                },
                                'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                                'attribute' => 'quantity',
                                'label' => Yii::t('message', 'frontend.views.client.anal.quantity', ['ru' => 'Кол-во']),
                                'value' => function ($data) {
                                    return number_format($data['quantity'], 2, '.', '');
                                },
                                'contentOptions' => ['style' => 'vertical-align:middle;width:18%'],
                            ],
                            [
                                'attribute' => 'price',
                                'format' => 'raw',
                                'label' => Yii::t('message', 'frontend.views.client.anal.total_two', ['ru' => 'Итого']),
                                'value' => function ($data) {
                                    return (float)$data['price'] . ' ' . $data['iso_code'];
                                },
                                'contentOptions' => ['style' => 'vertical-align:middle;font-weight:bold;width:25%'],
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
                            'condensed' => false,
                            'resizableColumns' => false,
                            'responsive' => false,
                            'hover' => true,
                            'summary' => false,
                        ]);
                        ?>
                        <?php //Pjax::end(); ?>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>


            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('app', 'frontend.analytics.price_changes', ['ru' => 'Статистика изменения цен']) ?></h3>
                    </div>
                    <div class="box-body" style="display: block;">

                        <?php
                        $form = ActiveForm::begin([
                            'method' => 'post',
                            'action' => Url::to(['client/price-stat'])
                        ]);
                        ?>

                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <?= Html::label(Yii::t('message', 'frontend.views.client.anal.vendor', ['ru' => 'Поставщик']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                                <?= Html::dropDownList('supplier', null, $filter_get_supplier, ['prompt' => Yii::t('message', 'frontend.views.client.anal.all', ['ru' => 'Все поставщики']), 'class' => 'form-control'])
                                ?>
                            </div>

                            <div class="col-lg-3 col-md-3 col-md-offset-1 col-sm-12">
                                <?= Html::label(Yii::t('message', 'frontend.views.layouts.header.businesses', ['ru' => 'БИЗНЕСЫ']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                                <?=
                                Html::dropDownList('business', null, $businessArray, ['prompt' => Yii::t('app', 'Все', ['ru' => 'Все']), 'class' => 'form-control'])
                                ?>
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <?= Html::label('', null, ['class' => 'label', 'style' => 'color:#555']) ?>
                                <?=
                                Html::checkbox('show_not_changed_price', true, ['label' => Yii::t('app', 'frontend.views.client.anal.show.not_changed_price', ['ru' => 'Показывать позиции, где цена не менялась'])])
                                ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4 col-md-offset-4">
                                <?= Html::label(Yii::t('app', 'franchise.views.anal.date_from_two', ['ru' => 'Начальная дата ']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                                <?=
                                DatePicker::widget([
                                    'name' => 'filter_from_date_price_stat',
                                    'id' => 'filter-date-price-stat',
                                    'value' => $filter_from_date_two,
                                    'type' => DatePicker::TYPE_INPUT,
                                    'name2' => 'filter_to_date_price_stat',
                                    'value2' => $filter_to_date,
                                    'separator' => '-',
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
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4 col-md-offset-4">
                                <?= Html::submitButton(Yii::t('app', 'franchise.views.site.download_three', ['ru' => 'Скачать']) . " (XLS)", ['id' => 'priceStat', 'class' => 'form-control clear_filters btn btn-outline-success teaser']) ?>
                            </div>
                        </div>

                        <?php
                        ActiveForm::end();
                        ?>

                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>

        </div>

        <?php
        $arr_create_at = json_encode($arr_create_at);
        $arr_price = json_encode($arr_price);
        $value = Yii::t('message', 'frontend.views.client.anal.sell_value', ['ru' => "Объем продаж"]);
        ?>

        <?php
        $customJs = <<< JS
    
    
// Get context with jQuery - using jQuery's .get() method.
var areaChartCanvas = $("#areaChart").get(0).getContext("2d");
// This will get the first returned node in the jQuery collection.
var areaChart = new Chart(areaChartCanvas);
var areaChartData = {
      labels: $arr_create_at,
      datasets: [
        {
          label: "$value",
          fillColor: "rgba(0,0,0,.05)",
          strokeColor: "#84bf76",
          pointColor: "#000",
          pointStrokeColor: "#000",
          pointHighlightFill: "#000",
          pointHighlightStroke: "#000",
          data: $arr_price
        }
      ]
    };

var areaChartOptions = {
      //Boolean - If we should show the scale at all
      showScale: true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines: true,
      //String - Colour of the grid lines
      scaleGridLineColor: "rgba(0,0,0,.05)",
      //Number - Width of the grid lines
      scaleGridLineWidth: 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines: true,
      //Boolean - Whether the line is curved between points
      bezierCurve: true,
      //Number - Tension of the bezier curve between points
      bezierCurveTension: 0.3,
      //Boolean - Whether to show a dot for each point
      pointDot: false,
      //Number - Radius of each point dot in pixels
      pointDotRadius: 5,
      //Number - Pixel width of point dot stroke
      pointDotStrokeWidth: 1,
      //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
      pointHitDetectionRadius: 20,
      //Boolean - Whether to show a stroke for datasets
      datasetStroke: true,
      //Number - Pixel width of dataset stroke
      datasetStrokeWidth: 2,
      //Boolean - Whether to fill the dataset with a color
      datasetFill: true,
      //String - A legend template
      legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
      //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
      maintainAspectRatio: true,
      //Boolean - whether to make the chart responsive to window resizing
      responsive: true
    };
  //Create the line chart
    areaChart.Line(areaChartData, areaChartOptions);
        
        
var pieData = $ vendors_total_price;
var Options = {responsive: true}
var context = document.getElementById('pieChart').getContext('2d');
var skillsChart = new Chart(context).Pie(pieData, Options);
/*if(!areaChartData.labels.length) {
    //$('.chart').html('Нет данных')
}*/
     

//-------------
//- BAR CHART -
//-------------
var barChartData = {
  labels: $ chart_bar_label,
  datasets: [{
    fillColor: "rgba(0,0,0,.05)",
    strokeColor: "#84bf76",
    data: $ chart_bar_value
  }]
}

var index = 11;
var ctx = document.getElementById("barChart").getContext("2d");
var barChartDemo = new Chart(ctx).Bar(barChartData, {
  responsive: true,
  barValueSpacing: 2,
  ToolTipTitle: false
});
JS;
        //$this->registerJs($customJs, View::POS_READY);
        ?>

        <?php Pjax::end(); ?>
    </section>
<?php
$filter_clear_from_date = date("d-m-Y", strtotime(" -2 months"));
$filter_clear_to_date = date("d-m-Y");

$analyticsUrl = Url::to(['client/analytics']);
$analyticsCurrencyUrl = Url::to(['client/ajax-update-currency']);

$customJs = <<< JS

$(document).on("click", ".alLi", function() {
      $('#w2').remove();
      $('#alWrapper').append('<canvas id="w2" width="350" height="350" style="display: block; width: 350px; height: 350px;" class="chartjs-render-monitor"></canvas>');
      var pieChart = $('#w2');
      var id = $(this).attr("color-id");
      var json_vendors_colors = $.extend([], json_vendors_colors_source);
      var json_vendors_total_price = $.extend([], json_vendors_total_price_source);
      var json_vendors_labels = json_vendors_labels_source;
      $(".alLi").each(function() {
          var color_id = $(this).attr("color-id");
          if($(this).hasClass("alStrikethrough")){
              if(color_id == id){
                  $(this).removeClass("alStrikethrough");
              }else{
                delete json_vendors_colors[color_id];
                 delete json_vendors_total_price[color_id]
              }
          }else{
              if(color_id == id){
                  $(this).addClass("alStrikethrough");
                  delete json_vendors_colors[color_id];
                 delete json_vendors_total_price[color_id];
              }
          }
      });

      var newChart = new Chart(pieChart, {
        type: 'pie',
        data: {
                  labels : json_vendors_labels,
                  datasets : [
                                {
                                  data : json_vendors_total_price,
                                  backgroundColor : json_vendors_colors,
                                  hoverBackgroundColor : json_vendors_colors,
                                },
                      ]      
              },           
        options: {
            legend : false,
            height : 382,
            width : 382
        },
    });
});

$(document).on("change", "#filter-date,#filter-date-2", function () {
$("#filter-date,#filter-date-2#filter_currency").attr('disabled','disabled')      
var filter_from_date =  $("#filter-date").val();
var filter_to_date =  $("#filter-date-2").val();        
var filter_currency =  $("#filter_currency").val(); 
    $.ajax({
     type: 'get',
     push: true,
     timeout: 0,
     url: "$analyticsCurrencyUrl",
     container: "#alCurrencies",
     data: {
         filter_from_date: filter_from_date,
         filter_to_date: filter_to_date,
         filter_currency: filter_currency,
         organization_id: "$organizationId"
           }
   }).done(function(data) { $("#alCurrencies").html(data); $("#filter_status,#filter-date,#filter-date-2,#filter_supplier,#filter_employee,#filter_currency").removeAttr('disabled') })
});
    

$(document).on("change", "#filter_status,#filter-date,#filter-date-2,#filter_supplier,#filter_employee,#filter_currency", function () {
$("#filter_status,#filter-date,#filter-date-2,#filter_supplier,#filter_employee,#filter_currency").attr('disabled','disabled')      
var filter_status = $("#filter_status").val();
var filter_from_date =  $("#filter-date").val();
var filter_to_date =  $("#filter-date-2").val();
var filter_supplier =  $("#filter_supplier").val();
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
         filter_supplier: filter_supplier,
         filter_employee: filter_employee,
         filter_currency: filter_currency
           }
   }).done(function() { $("#filter_status,#filter-date,#filter-date-2,#filter_supplier,#filter_employee,#filter_currency").removeAttr('disabled') });
});

$("#reset").on("click", function () {
    $("#filter_status").val('');
    $("#filter-date").val('$filter_clear_from_date');
    $("#filter-date-2").val('$filter_clear_to_date');
    $("#filter_supplier").val('');     
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
         filter_supplier: '',
         filter_employee: '',
         filter_currency: 1
           }
   });
}); 
JS;
$this->registerJs($customJs, View::POS_READY);
