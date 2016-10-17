<?php
use common\models\Order;
use common\models\Organization;
use common\models\Profile;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
frontend\assets\AdminltePluginsAsset::register($this);
$this->registerCss('

');
?>
<div class="row">
    <div class="col-md-12">
      <!-- AREA CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Объем продаж</h3><br><small>ЗА ПОСЛЕДНИЕ 2 МЕСЯЦА</small>

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
    <div class="col-md-12">
      <!-- pie CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">История заказов</h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
            <div>
         <?php 
        $columns = [
    ['attribute' => 'id','label'=>'№','value'=>'id'],
    ['attribute' => 'client_id','label'=>'Ресторан','value'=>function($data) {
        return Organization::find()->where(['id'=>$data['client_id']])->one()->name;           
    }],
    ['attribute' => 'created_by_id','label'=>'Заказ создал','value'=>function($data) {
        return $data['created_by_id']?
             Profile::find()->where(['id'=>$data['created_by_id']])->one()->full_name :
             "";
    }],
    ['attribute' => 'accepted_by_id','label'=>'Заказ принял','value'=>function($data) {
        return $data['accepted_by_id']?
             Profile::find()->where(['id'=>$data['accepted_by_id']])->one()->full_name :
             "";
    }],
    [
        'format' => 'raw',
        'attribute' => 'total_price',
        'value' => function($data) {
            return $data['total_price'] . '<i class="fa fa-fw fa-rub"></i>';
        },
        'label' => 'Сумма',
    ],
    [
        'format' => 'raw',
        'attribute' => 'created_at',
        'value' => function($data) {
            $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
            return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
        },
        'label' => 'Дата создания',
    ],
    ['attribute' => 'status','label'=>'Статус','format' => 'raw','value' => function($data) {
                        switch ($data['status']) {
                            case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                            case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                $statusClass = 'new';
                                break;
                            case Order::STATUS_PROCESSING:
                                $statusClass = 'processing';
                                break;
                            case Order::STATUS_DONE:
                                $statusClass = 'done';
                                break;
                            case Order::STATUS_FORMING:
                                $statusClass = 'done';
                                break;
                            case Order::STATUS_REJECTED:
                            case Order::STATUS_CANCELLED:
                                $statusClass = 'cancelled';
                                break;
                        }
                        return '<span class="status ' . $statusClass . '"><i class="fa fa-circle-thin"></i> ' . Order::statusText($data['status']) . '</span>';//fa fa-circle-thin
                    },]
	];
        ?>
         <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'order-analytic-list',]); ?>
            <?=GridView::widget([
           'dataProvider' => $dataProvider,
           'filterPosition' => false,
           'columns' => $columns,
           'tableOptions' => ['class' => 'table no-margin'],
           'options' => ['class' => 'table-responsive'],
           'bordered' => false,
           'striped' => false,
           'condensed' => false,
           'responsive' => false,
           'hover' => true
           ]);
           ?> 
        <?php  Pjax::end(); ?>
            </div>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
</div>
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
JS;
$this->registerJs($customJs, View::POS_READY);
?>

