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
$this->title = 'Рабочий стол';
frontend\assets\AdminltePluginsAsset::register($this);
frontend\assets\TutorializeAsset::register($this);

$this->registerCss('
.box-analytics {border:1px solid #eee}.input-group.input-daterange .input-group-addon {border-left: 0px;}
tfoot tr{border-top:2px solid #ccc}
.info-box-content:hover{color:#65af53;
-webkit-box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.6);
-moz-box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.6);
box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.6);}
.info-box-content{color:#84bf76;
-webkit-box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);
-moz-box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);
box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);}
.order-history .info-box {box-shadow: none;}
.info-box {box-shadow: none;border:1px solid #eee;}
.info-box-text{margin: 0;padding-top:10px;color:#555}
.info-box-text{margin: 0;padding-top:10px;color:#555}
@media (min-width: 1200px){.moipost{padding-left:15px;padding-right:15px}}
.dash-small-box {
    border-radius: 3px;
    position: relative;
    display: block;
    margin-bottom: 20px;
    padding:20px;
    padding-top:1px;
    background:#fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow:hidden
}
.dash-small-box:hover{box-shadow: 0 1px 10px rgba(0,0,0,0.3);cursor:pointer}
.dash-small-box h3{font-size:28px;color:#3F3E3E}
.dash-small-box p{font-size:14px;color:#95989A}
.dash-small-box .btn{border-width:2px}
.dash-small-box .bg{
position: absolute;
left: 0;
top: 0;
width: 100%;
height: 100%;
}
.dash-small-box .bg {
 -moz-transition: all 1s ease-out;
 -o-transition: all 1s ease-out;
 -webkit-transition: all 1s ease-out;
 }
 
.dash-small-box:hover .bg{
 -webkit-transform: scale(1.1);
 -moz-transform: scale(1.1);
 -o-transform: scale(1.1);
 }
 .dash-box{
 border-radius: 3px;
    position: relative;
    display: block;
    margin-bottom: 20px;
    background:#fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow:hidden
}
 .tb-scroll{
overflow-y:scroll 
}
.table>tbody>tr>td {
    border-top: 1px solid #f4f4f4;
}
.table>tbody>tr:first-child>td {
    border-top: 1px solid #fff;
}
');
$this->registerCss('
@media (max-width: 1320px){
       th{
        min-width:140px;
        }
    }');
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Главная
        <small>Рабочий стол</small>
    </h1>
</section>
<section class="content">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 col-lg-4 col-sm-12 col-xs-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="dash-small-box step-order" data-target="order">
                                <div class="inner" style="position:relative;z-index:2">
                                  <h3>Создать заказ</h3>
                                  <p>у своих поставщиков</p>
                                </div>
                                <?= Html::a('Создать', ['order/create'],['class'=>'btn btn-outline-success' , 'style' => 'font-size:14px;position:relative;z-index:2']) ?>
    <div class="bg" style="
    background: url(images/dash.png) no-repeat bottom right;
    background-size: 140px;">
    </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="dash-small-box step-cart" data-target="checkout">
                                <div class="inner" style="position:relative;z-index:2">
                                  <h3>Корзина </h3>
                                  <p>заказов <b><?=$totalCart?></b></p>
                                </div>
                                <?= Html::a('Корзина', ['order/checkout'],['class'=>'btn btn-outline-success' , 'style' => 'font-size:14px;position:relative;z-index:2']) ?>
    <div class="bg" style="
    background: url(images/dash3.png) no-repeat center right;
    background-size: 150px;">
    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-4 col-sm-12 col-xs-12">
                <div class="row moipost">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="dash-small-box" data-target="request">
                                <div class="inner" style="position:relative;z-index:2">
                                  <h3>Создать заявку</h3>
                                  <p>для поставщиков</p>
                                </div>
                                <?= Html::a('Заявки', ['request/list'],['class'=>'btn btn-outline-success','style' => 'font-size:14px;position:relative;z-index:2']) ?>
    <div class="bg" style="
    background: url(images/dash1.png) no-repeat top right;
    background-size: 170px;">
    </div>                        
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="dash-small-box step-f-market" data-target="fmarket">
                                <div class="dash-title-border"></div>
                                <div class="inner" style="position:relative;z-index:2">
                                  <h3>F-Market</h3>
                                  <p>доступно для заказа товаров <b><?=$count_products_from_mp ?></b></p>
                                </div>
                                <?= Html::a('F-Market', 'https://market.f-keeper.ru',['target'=>'_blank','class'=>'btn btn-outline-success' , 'style' => 'font-size:14px;position:relative;z-index:2']) ?>
    <div class="bg" style="
    background: url(images/dash2.png) no-repeat bottom right;
    background-size: 120px;">
    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-4 col-sm-12 col-xs-12 ">
                <div class="row">
                    <div class="dash-box step-vendors-list">
                        <div class="box-header with-border">
                            <?= Html::a('<span style="color:#3F3E3E">Мои</span> поставщики <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i>', ['client/suppliers'],['class'=>'step-manage-vendors' , 'style' => 'font-size: 18px;']) ?>
    
                        </div>
                        <div class="box-body" style="height: 268px;overflow-y:scroll">
                        <?php
                        $columns1 = [
                        ['attribute' => '','format'=>'raw','header' => false,'value'=>function($data) {
                            $url = empty($data->picture) ? Yii::getAlias('@web') . \common\models\Organization::DEFAULT_VENDOR_AVATAR : $data->pictureUrl;
                            return Html::img( $url, ['style' => 'width:70px'] );
                        }],
                        ['attribute' => 'name','value'=>'name', 'label' => 'Поставщики'],
                        ['attribute' => '','format'=>'raw','header' => false,'value'=>function($data) {
                            return Html::a('<i class="fa fa-shopping-cart m-r-xs"></i> Заказать', ['order/create',
                                'OrderCatalogSearch[searchString]'=>"",
                                'OrderCatalogSearch[selectedCategory]'=>"",
                                'OrderCatalogSearch[selectedVendor]'=>$data['supp_org_id'],
                                ],['class'=>'btn btn-outline-default btn-sm pull-right','data-pjax'=>0,'style'=>'border-width:2px;border-color:#3F3E3E']);           
                        }]
                        ];
                        ?>
                        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'suppliers-list',]); ?>
                            <?=GridView::widget([
                           'dataProvider' => $suppliers_dataProvider,
                           'filterPosition' => false,
                           'columns' => $columns1,
                           'showHeader' => false,
                           'tableOptions' => ['class' => 'table no-margin'],
                           'options' => ['class' => 'table-responsive'],
                           'bordered' => false,
                           'striped' => false,
                           'condensed' => false,
                           'responsive' => true,
                           'resizableColumns'=>false,
                           'hover' => true,
                           'summary' => false,
                            /*'pager' => [
                                'maxButtonCount'=>5,    // Set maximum number of page buttons that can be displayed
                            ],*/
                           ]);
                           ?> 
                        <?php  Pjax::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
<div class="row hidden-xs">
    <div class="col-md-4">
      
      <!-- /.box -->
    </div>
    <!--div class="col-md-8">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Аналитика заказов</h3>

          <div class="box-tools pull-right">
            <?= Html::a('Аналитика', ['client/analytics'],['class'=>'btn btn-success btn-sm']) ?>
          </div>
        </div>
        <div class="box-body" style="display: block;">
            <div class="chart">
            <canvas id="areaChart" style="height: 282px; width: 574px;" height="282" width="574"></canvas>
          </div> 
        </div>
      </div>
    </div-->
</div>
<div class="row">
    <div class="col-md-12">
      <div class="box box-info" style="border: none;">
        <div class="box-header with-border">
          <?= Html::a('<span style="color:#3F3E3E">История</span> заказов <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i>', ['order/index'],['class'=>'' , 'style' => 'font-size: 18px;']) ?>
    
        </div>
        <div class="box-body" style="display: block;">
          <?php 
        $columns = [
    ['attribute' => 'id','label'=>'№','value'=>'id'],
    ['attribute' => 'vendor_id','label'=>'Поставщик','value'=>function($data) {
        return Organization::find()->where(['id'=>$data['vendor_id']])->one()->name;           
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
            return "<strong>".$data['total_price'] . '<i class="fa fa-fw fa-rub"></i></strong>';
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
                        return '<span class="status ' . $statusClass . '">' . Order::statusText($data['status']) . '</span>';//<i class="fa fa-circle-thin"></i> 
                    },],
                            [
                                'format' => 'raw',
                                'value' => function($data) {
                                    switch ($data['status']) {
                                        case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                                        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                        case Order::STATUS_PROCESSING:
                                        case Order::STATUS_DONE:
                                        case Order::STATUS_REJECTED:
                                        case Order::STATUS_CANCELLED:
                                            return Html::a('<i class="fa fa-refresh"></i>', ['order/repeat', 'id' => $data['id']], [
                                                        'class' => 'reorder',
                                                        'data' => [
                                                            'toggle' => 'tooltip',
                                                            'original-title' => 'Повторить заказ',
                                                        ],
                                            ]);
                                            break;
                                    }
                                    return '';
                                },
                                        'contentOptions' => ['class' => 'text-center'],
                                        'headerOptions' => ['style' => 'width: 20px;']
                                    ],
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
                
           'resizableColumns'=>false,
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
/*
//$chart_dates =   json_encode(array_reverse($chart_dates));
//$chart_price =   json_encode(array_reverse($chart_price));

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

 */
$user = Yii::$app->user->identity;
$organization = $user->organization;
$vendorsText = strpos($user->email, '@delivery-club.ru') ? "Список ваших поставщиков. Специально для Вас мы добавили несколько рекомендованных нами поставщиков" : "Список ваших поставщиков.";
    $customJs = <<< JS
    $(document).on('click','.dash-small-box', function(){
    var targetUrl = $(this).attr('data-target');
        if(targetUrl == 'checkout'){location.href = 'index.php?r=order/checkout';}
        if(targetUrl == 'order'){location.href = 'index.php?r=order/create';}
        if(targetUrl == 'fmarket'){window.open('https://market.f-keeper.ru');}
    }) 
JS;
$this->registerJs($customJs, View::POS_READY);

if ($organization->step == Organization::STEP_TUTORIAL) {
    $turnoffTutorial = Url::to(['/site/ajax-tutorial-off']);
    $customJs2 = <<< JS
    $(document).on('click','.dash-small-box', function(){
    var targetUrl = $(this).attr('data-target');
        if(targetUrl == 'checkout'){location.href = 'index.php?r=order/checkout';}
        if(targetUrl == 'order'){location.href = 'index.php?r=order/create';}
        if(targetUrl == 'fmarket'){window.open('https://market.f-keeper.ru');}
    }); 

                    var _slides = [{
                            title: '<img src="images/welcome-client-bg.png" class="welcome-header-image" />',
                            content: '{$this->render("welcome")}',
                            position: 'center',
                            overlayMode: 'all',
                            selector: 'html',
                            width: '450px',
                            height: '460px',
                    },
                    {
                            title: '&nbsp;',
                            content: 'Создание заказа из прайс-листов ваших поставщиков.',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.step-order',
                    },
                    {
                            title: '&nbsp;',
                            content: 'Ваша корзина. Здесь хранятся заказы, готовые для отправки поставщику.',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.step-cart',
                    },
                    {
                            title: '&nbsp;',
                            content: '$vendorsText',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.step-vendors-list',
                    },
                    {
                            title: '&nbsp;',
                            content: 'Вы всегда можете добавить поставщиков, с которыми уже работаете.',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.step-manage-vendors',
                    },
                    {
                            title: '&nbsp;',
                            content: 'Или найти новых с помощью сервиса F-Market.',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.step-f-market',
                    },
                    {
                            title: '&nbsp;',
                            content: 'Навигация по системе.',
                            position: 'right-center',
                            overlayMode: 'focus',
                            selector: '.sidebar',
                    },
                    {
                            title: '&nbsp;',
                            content: 'Вы всегда можете пройти обучение заново.',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.repeat-tutorial',
                    }
                        ];

                    $.tutorialize({
                            slides: _slides,
                            bgColor: '#fff',
                            buttonBgColor: '#84bf76',
                            buttonFontColor: '#fff',
                            fontColor: '#3f3e3e',
                            showClose: true,
                            labelEnd: 'Завершить',
                            labelNext: 'Вперед',
                            labelPrevious: 'Назад',
                            labelStart: 'Начать работу',
                            arrowPath: './arrows/arrow-green.png',
                            fontSize: '14px',
                            onStop: function(currentSlideIndex, slideData, slideDom){
                                    $.get(
                                        '{$turnoffTutorial}'
                                    );
                                },
                    });

                    $.tutorialize.start();

JS;
    $this->registerJs($customJs2, View::POS_READY);
}
?>
