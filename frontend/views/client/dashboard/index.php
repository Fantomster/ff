<?php
use yii\widgets\Breadcrumbs;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use common\models\Order;
use common\models\Organization;
use common\models\Profile;
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
        Главная
        <small>Рабочий стол</small>
    </h1>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-body order-history">
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <a href="index.php?r=order/create">
                    <div class="info-box-content">
                        <i class="fa fa-truck" style="font-size: 28px;"></i>
                        <p class="info-box-text">Разместить заказ</p>
                    </div>                    
                </a>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <a href="index.php?r=client/suppliers">
                    <div class="info-box-content">
                        <i class="fa fa-users" style="font-size: 28px;"></i>
                        <p class="info-box-text">Управление вашими поставщиками</p>
                    </div>                    
                </a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Мои поставщики</h3>

          <div class="box-tools pull-right">
            <?= Html::a('Мои поставщики', ['client/suppliers'],['class'=>'btn btn-success btn-sm']) ?>
          </div>
        </div>
          <div class="box-header with-border">
            <?=Html::input('text', 'search', null, ['class' => 'form-control','placeholder'=>'Поиск','id'=>'search']) ?>
          </div>
        <div class="box-body" style="display: block;">
        <?php
        $columns1 = [
        ['attribute' => 'name','label'=>'Поставщик','value'=>'name'],
        ['attribute' => 'client_id','format'=>'raw','label'=>'','value'=>function($data) {
            return Html::a('заказ', ['order/create',
                'OrderCatalogSearch[searchString]'=>"",
                'OrderCatalogSearch[selectedCategory]'=>"",
                'OrderCatalogSearch[selectedVendor]'=>$data['supp_org_id'],
                ],['class'=>'btn btn-outline-success btn-sm pull-right','data-pjax'=>0]);           
        }]
        ];
        ?>
        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'suppliers-list',]); ?>
            <?=GridView::widget([
           'dataProvider' => $suppliers_dataProvider,
           'filterPosition' => false,
           'columns' => $columns1,
           'tableOptions' => ['class' => 'table no-margin'],
           'options' => ['class' => 'table-responsive'],
           'bordered' => false,
           'striped' => false,
           'condensed' => false,
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
    <div class="col-md-8">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Аналитика заказов</h3>

          <div class="box-tools pull-right">
            <?= Html::a('Аналитика', ['client/analytics'],['class'=>'btn btn-success btn-sm']) ?>
          </div>
        </div>
        <div class="box-body" style="display: block;">
            <canvas id="areaChart" style="height: 200px; width: 514px;" height="200" width="514"></canvas>  
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
</div>
<div class="row">
    <div class="col-md-12">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">История заказов</h3>
          <div class="box-tools pull-right">
            <?= Html::a('История заказов', ['order/index'],['class'=>'btn btn-success btn-sm']) ?>
          </div>
        </div>
        <div class="box-body" style="display: block;">
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
           'hover' => true,
           'summary' => false,
           'rowOptions' => function ($model, $key, $index, $grid) {
                return ['id' => $model['id'],'style'=>'cursor:pointer', 'onclick' => 'window.location.replace("index.php?r=order/view&id="+this.id);'];
            },
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
$chart_dates =   json_encode(array_reverse($chart_dates));
$chart_price =   json_encode(array_reverse($chart_price));
$customJs = <<< JS
var timer;
$('#search').on("keyup put paste change", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'get',
        push: false,
        url: 'index.php?r=client/index',
        container: '#suppliers-list',
        data: { searchString: $('#search').val()}
      })
   }, 700);
});    
// Get context with jQuery - using jQuery's .get() method.
var areaChartCanvas = $("#areaChart").get(0).getContext("2d");
// This will get the first returned node in the jQuery collection.
var areaChart = new Chart(areaChartCanvas);
var areaChartData = {
      labels: $chart_dates,
      datasets: [
        {
          label: $chart_price,
          fillColor: "rgba(0,0,0,.05)",
          strokeColor: "#84bf76",
          pointColor: "#000",
          pointStrokeColor: "#000",
          pointHighlightFill: "#000",
          pointHighlightStroke: "#000",
          data: $chart_price
        }
      ]
    };

var areaChartOptions = {
    tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value%>",
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
      //legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
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