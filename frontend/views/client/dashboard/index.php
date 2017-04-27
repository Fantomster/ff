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
tr:hover {
    cursor: pointer;
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
    background: url(/images/dash.png) no-repeat bottom right;
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
    background: url(/images/dash3.png) no-repeat center right;
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
    background: url(/images/dash1.png) no-repeat top right;
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
    background: url(/images/dash2.png) no-repeat bottom right;
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
    </div>
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
            [
                'attribute' => 'id',
                'value' => 'id',
                'label' => '№',
            ],
            [
                'attribute' => 'vendor.name',
                'value' => 'vendor.name',
                'label' => 'Поставщик',
            ],
            [
                'attribute' => 'createdByProfile.full_name',
                'value' => 'createdByProfile.full_name',
                'label' => 'Заказ создал',
            ],
            [
                'attribute' => 'acceptedByProfile.full_name',
                'value' => 'acceptedByProfile.full_name',
                'label' => 'Заказ принял',
            ],
            [
                'format' => 'raw',
                'attribute' => 'total_price',
                'value' => function($data) {
                    return "<b>$data->total_price</b><i class='fa fa-fw fa-rub'></i>";
                },
                'label' => 'Сумма',
            ],
            [
                'format' => 'raw',
                'attribute' => 'created_at',
                'value' => function($data) {
                    $date = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                },
                'label' => 'Дата создания',
            ],
            [
                'format' => 'raw',
                'attribute' => 'status',
                'value' => function($data) {
                    switch ($data->status) {
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
                    return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>'; //<i class="fa fa-circle-thin"></i> 
                },
                'label' => 'Статус',
            ],
            [
                'format' => 'raw',
                'value' => function($data) {
                    switch ($data['status']) {
                        case Order::STATUS_DONE:
                        case Order::STATUS_REJECTED:
                        case Order::STATUS_CANCELLED:
                            return Html::a('Повторить', '#' , [
                                                        'class' => 'reorder btn btn-outline-processing',
                                                        'data' => [
                                                            'toggle' => 'tooltip',
                                                            'original-title' => 'Повторить заказ',
                                                            'url' => Url::to(['order/repeat', 'id' => $data->id])
                                                        ],
                                            ]);
                            break;
                        case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                        case Order::STATUS_PROCESSING:
                            if ($data->isObsolete) {
                                return Html::a('Завершить', '#' , [
                                        'class' => 'complete btn btn-outline-success',
                                        'data' => [
                                            'toggle' => 'tooltip',
                                            'original-title' => 'Завершить заказ',
                                            'url' => Url::to(['order/complete-obsolete', 'id' => $data->id])
                                        ],
                                ]);
                            }
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
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                'filterPosition' => false,
                'columns' => $columns,
                'tableOptions' => ['class' => 'table no-margin table-hover'],
                'options' => ['class' => 'table-responsive'],
                'bordered' => false,
                'striped' => false,
                'condensed' => false,
                'responsive' => false,
                'hover' => true,
                'summary' => false,

                'resizableColumns'=>false,
                'rowOptions' => function ($model, $key, $index, $grid) {
                    return ['data-url' => Url::to(['order/view', 'id' => $model->id])];
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

$user = Yii::$app->user->identity;
$organization = $user->organization;
$vendorsText = strpos($user->email, '@delivery-club.ru') ? "Список ваших поставщиков. Специально для Вас мы добавили несколько рекомендованных нами поставщиков" : "Список ваших поставщиков.";

$checkoutUrl = Url::to(['order/checkout']);
$createUrl = Url::to(['order/create']);
$requestUrl = Url::to(['request/list']);

    $customJs = <<< JS
    $(document).on('click','.dash-small-box', function(){
    var targetUrl = $(this).attr('data-target');
        if(targetUrl == 'checkout'){location.href = '$checkoutUrl';}
        if(targetUrl == 'order'){location.href = '$createUrl';}
        if(targetUrl == 'request'){location.href = '$requestUrl';}
        if(targetUrl == 'fmarket'){window.open('https://market.f-keeper.ru');}
    }) 
            
    $(document).on("click", "td", function (e) {
        if ($(this).find("a").hasClass("reorder") || $(this).find("a").hasClass("complete")) {
            return true;
        }
        var url = $(this).parent("tr").data("url");
        if (url !== undefined) {
            location.href = url;
        }
    });
            
    $(document).on("click", ".reorder, .complete", function(e) {
        e.preventDefault();
        clicked = $(this);
        swal({
            title: clicked.data("original-title") + "?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Да",
            cancelButtonText: "Отмена",
            showLoaderOnConfirm: true,
        }).then(function() {
            document.location = clicked.data("url")
        });
    });
JS;
$this->registerJs($customJs, View::POS_READY);

if ($organization->step == Organization::STEP_TUTORIAL) {
    $turnoffTutorial = Url::to(['/site/ajax-tutorial-off']);
    $customJs2 = <<< JS
    $(document).on('click','.dash-small-box', function(){
    var targetUrl = $(this).attr('data-target');
        if(targetUrl == 'checkout'){location.href = '$checkoutUrl';}
        if(targetUrl == 'order'){location.href = '$createUrl';}
        if(targetUrl == 'fmarket'){window.open('https://market.f-keeper.ru');}
    }); 

                    var _slides = [{
                            title: '<img src="/images/welcome-client-bg.png" class="welcome-header-image" />',
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
