<?php
use yii\widgets\Breadcrumbs;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
frontend\assets\AdminltePluginsAsset::register($this);
$this->registerCss('
.box-analytics {border:1px solid #eee}.input-group.input-daterange .input-group-addon {
    border-left: 0px;
}
tfoot tr{border-top:2px solid #ccc}
.info-box-content:hover{color:#378a5f;}
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
');
?>
<section class="content-header">
    <h1>
        <i class="fa fa-signal"></i> Аналитика
        <small>Аналитика в одном месте</small>
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
                    <span class="info-box-number"><?=$header_info_zakaz;?></span>
                    <span class="info-box-text">Всего заказов</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$header_info_purchases;?></span>
                    <span class="info-box-text">Всего Закупок</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$header_info_suppliers?></span>
                    <span class="info-box-text">Всего поставщиков</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$header_info_items;?></span>
                    <span class="info-box-text">Позиций</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6">
<?= Html::label('Поставщик', null, ['class' => 'label','style'=>'color:#555']) ?>
<?= Html::dropDownList('filter_supplier', null,
                            $filter_get_supplier,['prompt' => 'Все поставщики','class' => 'form-control','id'=>'filter_supplier']) ?>        
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6">
<?= Html::label('Сотрудник', null, ['class' => 'label','style'=>'color:#555']) ?>
<?= Html::dropDownList('filter_employee', null,
                            $filter_get_employee,['prompt' => 'Все сотрудники','class' => 'form-control','id'=>'filter_employee']) ?>        
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6">
<?= Html::label('Статус заказа', null, ['class' => 'label','style'=>'color:#555']) ?>
<?= Html::dropDownList('filter_status', null,
                            [
                                '1' => 'Ожидание от поставщика',
                                '2' => 'Ожидание от заказчика',
                                '3' => 'Выполняется',
                                '4' => 'Завершен',
                                '5' => 'Отменен заказчиком',
                                '6' => 'Отменен поставщиком',
                            ],['prompt' => 'Все','class' => 'form-control','id'=>'filter_status']) ?>         
        </div>
        <div class="col-lg-5 col-md-6 col-sm-6"> 
            <?php 
            $layout = <<< HTML
                {input1}
                {separator}
                {input2}
HTML;
            ?>
            <?=Html::label('Начальная дата / Конечная дата', null, ['class' => 'label','style'=>'color:#555']) ?>
            <?=DatePicker::widget([
                'name' => 'filter_from_date',
                'id'=>'filter-date',
                'value' => $filter_from_date,
                'type' => DatePicker::TYPE_RANGE,
                'name2' => 'filter_to_date',
                'value2' => $filter_to_date,
                'separator' => '<i class="fa fa-arrows-h" aria-hidden="true"></i>',
                'layout' => $layout,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd-mm-yyyy',
                    'todayHighlight' => true,
                    'endDate' =>  "0d",
                ],
                'removeButton' => false,
            ]);
            ?>
        </div>
        <div class="col-lg-1 col-md-1 col-sm-2">
<?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
<?= Html::button('<i class="fa fa-times" aria-hidden="true"></i>', ['id'=>'reset','class' => 'form-control clear_filters btn btn-outline-danger teaser']) ?>        
        </div>
    </div>
</div>
<?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'analytics-list',]); ?>
<div class="row">
    <div class="col-md-6">
      <!-- AREA CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Объем заказов</h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
          <div class="chart">
            <canvas id="areaChart" style="height: 282px; width: 574px;" height="282" width="574"></canvas>
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
          <h3 class="box-title">Заказы по поставщикам</h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
          <div class="chart">
            <canvas id="barChart" style="height: 282px; width: 574px;" height="282" width="574"></canvas>
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
          <h3 class="box-title">Объем по поставщикам</h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
          <div class="chart">
          <canvas id="pieChart" style="height: 282px; width: 574px;" height="282" width="574"></canvas>  
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
          <h3 class="box-title">Статистика по товарам</h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
          <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'product-analytic-list',]); ?>
             <?php 
            $columns = [
                [
                'attribute' => 'product_id',
                'label'=>'Товар',
                'value'=>function ($data) {
                    return \common\models\CatalogBaseGoods::find()->where(['id'=>$data['product_id']])->one()->product;
                },
                'contentOptions' => ['style' => 'vertical-align:middle;'],
                ],
                [
                'attribute' => 'quantity',
                'label'=>'Кол-во',
                'value'=>'quantity',
                'contentOptions' => ['style' => 'vertical-align:middle;'],
                ],
                [
                'attribute' => 'price',
                'format'=>'raw',
                'label'=>'Итого',
                'value'=>function ($data) { return $data['price']."<i class=\"fa fa-fw fa-rub\"></i>";},
                'contentOptions' => ['style' => 'vertical-align:middle;'],
                ]
            ];
            ?>
             <?=GridView::widget([
            'dataProvider' => $dataProvider,
            'filterPosition' => false,
            'columns' => $columns,
            'tableOptions' => ['class' => 'table no-margin'],
            'options' => ['class' => 'table-responsive'],
            'bordered' => false,
            'striped' => false,
            'condensed' => false,
           'resizableColumns'=>false,
            'responsive' => false,
            'hover' => true,
            'summary' => false,
            ]);
            ?> 
            <?php  Pjax::end(); ?>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
</div>
</section>
<?php

$arr_create_at =   json_encode($arr_create_at);
$arr_price =   json_encode($arr_price);
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
          label: "Объем продаж",
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
        
        
var pieData = $vendors_total_price;
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
  labels: $chart_bar_label,
  datasets: [{
    fillColor: "rgba(0,0,0,.05)",
    strokeColor: "#84bf76",
    data: $chart_bar_value
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
$this->registerJs($customJs, View::POS_READY);
?>
<?php Pjax::end(); ?>
<?php
$filter_clear_from_date = date("d-m-Y", strtotime(" -2 months"));
$filter_clear_to_date = date("d-m-Y");
$customJs = <<< JS
$("#filter_status,#filter-date,#filter-date-2,#filter_supplier,#filter_employee").on("change", function () {
$("#filter_status,#filter-date,#filter-date-2,#filter_supplier,#filter_employee").attr('disabled','disabled')      
var filter_status = $("#filter_status").val();
var filter_from_date =  $("#filter-date").val();
var filter_to_date =  $("#filter-date-2").val();
var filter_supplier =  $("#filter_supplier").val();
var filter_employee =  $("#filter_employee").val();        
    $.pjax({
     type: 'GET',
     push: false,
     timeout: 10000,
     url: "index.php?r=client/analytics",
     container: "#analytics-list",
     data: {
         filter_status: filter_status,
         filter_from_date: filter_from_date,
         filter_to_date: filter_to_date,
         filter_supplier: filter_supplier,
         filter_employee: filter_employee,
           }
   }).done(function() { $("#filter_status,#filter-date,#filter-date-2,#filter_supplier,#filter_employee").removeAttr('disabled') });
});
$("#reset").on("click", function () {
    $("#filter_status").val('');
    $("#filter-date").val('$filter_clear_from_date');
    $("#filter-date-2").val('$filter_clear_to_date');
    $("#filter_supplier").val('');     
    $("#filter_employee").val(''); 
    $.pjax({
     type: 'GET',
     push: false,
     timeout: 10000,
     url: "index.php?r=client/analytics",
     container: "#analytics-list",
     data: {
         filter_status: '',
         filter_from_date: '$filter_clear_from_date',
         filter_to_date: '$filter_clear_to_date',
         filter_supplier: '',
         filter_employee: '',
           }
   });
}); 
JS;
$this->registerJs($customJs, View::POS_READY);