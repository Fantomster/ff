<?php

use common\models\OrderStatus;
use kartik\grid\GridView;
use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = Yii::t('message', 'frontend.views.client.index.desktop', ['ru' => 'Рабочий стол']);

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
    }
        #order-analytic-list a:not(.btn){color: #333;}
        .kv-table-wrap a{width: 100%; min-height: 17px; display: inline-block;}
        ');
$user = Yii::$app->user->identity;
$organization = $user->organization;

$infoUrl = Url::to(['/site/ajax-set-info']);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('message', 'frontend.views.client.index.main', ['ru' => 'Главная']) ?>
        <small><?= Yii::t('message', 'frontend.views.client.index.desktop_two', ['ru' => 'Рабочий стол']) ?></small>
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
                                    <h3><?= Yii::t('message', 'frontend.views.client.index.create', ['ru' => 'Создать заказ']) ?></h3>
                                    <p><?= Yii::t('message', 'frontend.views.client.index.self', ['ru' => 'у своих поставщиков']) ?></p>
                                </div>
                                <?= Html::a(Yii::t('message', 'frontend.views.client.index.create_two', ['ru' => 'Создать']), ['order/create'], ['class' => 'btn btn-outline-success', 'style' => 'font-size:14px;position:relative;z-index:2']) ?>
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
                                    <h3><?= Yii::t('message', 'frontend.views.client.index.basket', ['ru' => 'Корзина']) ?> </h3>
                                    <p><?= Yii::t('app', 'frontend.views.client.index.orders', ['ru' => 'заказов']) ?> <b><?= $totalCart ?></b></p>
                                </div>
                                <?= Html::a(Yii::t('message', 'frontend.views.client.index.basket_two', ['ru' => 'Корзина']), ['order/checkout'], ['class' => 'btn btn-outline-success', 'style' => 'font-size:14px;position:relative;z-index:2']) ?>
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
                                    <h3><?= Yii::t('message', 'frontend.views.client.index.request', ['ru' => 'Создать заявку']) ?></h3>
                                    <p><?= Yii::t('message', 'frontend.views.client.index.for_vendors', ['ru' => 'для поставщиков']) ?></p>
                                </div>
                                <?= Html::a(Yii::t('message', 'frontend.views.client.index.requests', ['ru' => 'Заявки']), '#', ['class' => 'btn btn-outline-success', 'style' => 'font-size:14px;position:relative;z-index:2']) ?>
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
                                    <h3>MixMarket</h3>
                                    <p><?= Yii::t('message', 'frontend.views.client.index.avail', ['ru' => 'доступно для заказа товаров']) ?> <b><?= $count_products_from_mp ?></b></p>
                                </div>
                                <?= Html::a('MixMarket', 'https://market.mixcart.ru', ['target' => '_blank', 'class' => 'btn btn-outline-success', 'style' => 'font-size:14px;position:relative;z-index:2']) ?>
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
                            <?= Html::a(Yii::t('message', 'frontend.views.client.index.my', ['ru' => '<span style="color:#3F3E3E">Мои</span> поставщики']) . '  <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i>', ['client/suppliers'], ['class' => 'step-manage-vendors', 'style' => 'font-size: 18px;']) ?>

                        </div>
                        <div class="box-body" style="height: 268px;overflow-y:scroll">
                            <?php
                            $columns1 = [
                                ['attribute' => '', 'format' => 'raw', 'header' => false, 'value' => function($data) {
                                        $url = empty($data->picture) ? Yii::getAlias('@web') . \common\models\Organization::DEFAULT_VENDOR_AVATAR : $data->pictureUrl;
                                        return Html::img($url, ['style' => 'width:70px']);
                                    }],
                                ['attribute' => 'name', 'value' => 'name', 'label' => Yii::t('message', 'frontend.views.client.index.vendors', ['ru' => 'Поставщики'])],
                                ['attribute' => '', 'format' => 'raw', 'header' => false, 'value' => function($data) {
                                        return Html::a('<i class="fa fa-shopping-cart m-r-xs"></i> ' . Yii::t('message', 'frontend.views.client.index.order', ['ru' => 'Заказать']), ['order/create',
                                                    'OrderCatalogSearch[searchString]' => "",
                                                    'OrderCatalogSearch[selectedCategory]' => "",
                                                    'OrderCatalogSearch[selectedVendor]' => $data['supp_org_id'],
                                                        ], ['class' => 'btn btn-outline-default btn-sm pull-right', 'data-pjax' => 0, 'style' => 'border-width:2px;border-color:#3F3E3E']);
                                    }]
                            ];
                            ?>
                            <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'suppliers-list',]); ?>
                            <?=
                            GridView::widget([
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
                                'resizableColumns' => false,
                                'hover' => true,
                                'summary' => false,
                                    /* 'pager' => [
                                      'maxButtonCount'=>5,    // Set maximum number of page buttons that can be displayed
                                      ], */
                            ]);
                            ?> 
                            <?php Pjax::end(); ?>
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
                    <?= Html::a(Yii::t('message', 'frontend.views.client.index.history', ['ru' => '<span style="color:#3F3E3E">История</span> заказов']) . '  <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i>', ['order/index'], ['class' => '', 'style' => 'font-size: 18px;']) ?>

                </div>
                <div class="box-body" style="display: block;">
                    <?php
                    $columns = [
                        [
                            'attribute' => 'id',
                            'label' => '№',
                            'contentOptions'   =>   ['class' => 'small_cell_id'],
                            'format' => 'raw',
                            'value' => function($data) {
                                return Html::a($data->id, Url::to(['order/view', 'id' => $data->id]), [ 'class' => 'target-blank', 'data-pjax'=>"0"]);
                            },
                        ],
                        $organization->type_id == Organization::TYPE_RESTAURANT ? [
                            'attribute' => 'vendor.name',
                            'value' => 'vendor.name',
                            'contentOptions'   =>   ['class' => 'small_cell_supp'],
                            'label' => Yii::t('message', 'frontend.views.order.vendor', ['ru'=>'Поставщик']),
                            'format' => 'raw',
                            'value' => function ($data) {
                                $text = "<div class='col-md-9'>";
                                $text .= Html::a($data->vendor->name, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                $text .= "</div><div class='col-md-3'>";
                                if (isset($data->vendor->ediOrganization->gln_code) && $data->vendor->ediOrganization->gln_code>0) {
                                    $alt = Yii::t('app', 'frontend.views.client.suppliers.edi_alt_text', ['ru' => 'Поставщик работает через систему электронного документооборота']);
                                    $text .= Html::img(Url::to('/img/edi-logo.png'), ['alt' => $alt, 'title' => $alt]);
                                }
                                $text .= "</div>";
                                return $text;
                            },
                        ] : [
                            'attribute' => 'client.name',
                            'value' => 'client.name',
                            'label' => Yii::t('message', 'frontend.views.order.rest_two', ['ru'=>'Ресторан']),
                            'format' => 'raw',
                            'value' => function($data) {
                                return Html::a($data->client->name, Url::to(['order/view', 'id' => $data->id]), [ 'class' => 'target-blank', 'data-pjax'=>"0"]);
                            },
                        ],
                        [
                            'attribute' => 'createdByProfile.full_name',
                            'value' => 'createdByProfile.full_name',
                            'label' => Yii::t('message', 'frontend.views.order.order_created_by', ['ru'=>'Заказ создал']),
                            'contentOptions'   =>   ['class' => 'small_cell_sozdal'],
                            'format' => 'raw',
                            'value' => function($data) {
                                return Html::a($data->createdByProfile->full_name ?? '', Url::to(['order/view', 'id' => $data->id]), [ 'class' => 'target-blank', 'data-pjax'=>"0"]);
                            },
                        ],
                        [
                            'attribute' => 'acceptedByProfile.full_name',
                            'value' => 'acceptedByProfile.full_name',
                            'format' => 'raw',
                            'value' => function($data) {
                                return Html::a($data->acceptedByProfile->full_name ?? '', Url::to(['order/view', 'id' => $data->id]), [ 'class' => 'target-blank', 'data-pjax'=>"0"]);
                            },
                            'label' => Yii::t('message', 'frontend.views.order.accepted_by', ['ru'=>'Заказ принял']),
                            'contentOptions'   =>   ['class' => 'small_cell_prinyal'],
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'total_price',
                            'value' => function($data) {
                                return Html::a("<b>$data->total_price</b> " . $data->currency->symbol ?? '', Url::to(['order/view', 'id' => $data->id]), [ 'class' => 'target-blank', 'data-pjax'=>"0"]);
                            },
                            'label' => Yii::t('message', 'frontend.views.order.summ', ['ru'=>'Сумма']),
                            'contentOptions'   =>   ['class' => 'small_cell_sum'],
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'created_at',
                            'value' => function($data) {
                                $date = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
                                return Html::a('<i class="fa fa-fw fa-calendar""></i> ' . $date ?? '', Url::to(['order/view', 'id' => $data->id]), [ 'class' => 'target-blank', 'data-pjax'=>"0"]);
                            },
                            'label' => Yii::t('message', 'frontend.views.order.creating_date', ['ru'=>'Дата создания']),
                            'contentOptions'   =>   ['style' => 'min-width:120px;'],

                        ],
                        [
                            'format'=>'raw',
                            'value' => function($data) {

                                $fdate = $data->actual_delivery ? $data->actual_delivery :
                                    ( $data->requested_delivery ? $data->requested_delivery :
                                        $data->updated_at);

                                $fdate = Yii::$app->formatter->asDatetime($fdate, "php:j M Y");
                                return Html::a('<i class="fa fa-fw fa-calendar""></i> '. $fdate ?? '', Url::to(['order/view', 'id' => $data->id]), [ 'class' => 'target-blank', 'data-pjax'=>"0"]);
                            },
                            'label' => Yii::t('message', 'frontend.views.order.final_date', ['ru'=>'Дата финальная']),
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'status',
                            'value' => function($data) {
                                switch ($data->status) {
                                    case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                                    case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                        $statusClass = 'new';
                                        break;
                                    case OrderStatus::STATUS_PROCESSING:
                                        $statusClass = 'processing';
                                        break;
                                    case OrderStatus::STATUS_DONE:
                                        $statusClass = 'done';
                                        break;
                                    case OrderStatus::STATUS_REJECTED:
                                    case OrderStatus::STATUS_CANCELLED:
                                        $statusClass = 'cancelled';
                                        break;
                                    default:
                                        $statusClass = 'new';
                                }
                                return Html::a('<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>' ?? '', Url::to(['order/view', 'id' => $data->id]), [ 'class' => 'target-blank', 'data-pjax'=>"0"]);
                            },
                            'label' => Yii::t('message', 'frontend.views.order.status_two', ['ru'=>'Статус']),
                            'contentOptions'   =>   ['class' => 'small_cell_status'],
                        ],
                        [
                            'format' => 'raw',
                            'value' => function($data) {
                                switch ($data['status']) {
                                    case OrderStatus::STATUS_DONE:
                                    case OrderStatus::STATUS_REJECTED:
                                    case OrderStatus::STATUS_CANCELLED:
                                        return Html::a(Yii::t('app', 'frontend.views.client.index.repeat', ['ru' => 'Повторить']), '#', [
                                                    'class' => 'reorder btn btn-outline-processing',
                                                    'data' => [
                                                        'toggle' => 'tooltip',
                                                        'original-title' => Yii::t('message', 'frontend.views.client.index.repeat', ['ru' => 'Повторить заказ']),
                                                        'url' => Url::to(['order/repeat', 'id' => $data->id])
                                                    ],
                                        ]);
                                        break;
                                    case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                                    case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                    case OrderStatus::STATUS_PROCESSING:
                                        if ($data->isObsolete) {
                                            $disabledString = (Yii::$app->user->identity->role_id == \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR) ? " disabled" : "";
                                            return Html::a(Yii::t('message', 'frontend.views.client.index.end', ['ru' => 'Завершить']), '#', [
                                                        'class' => "complete btn btn-outline-success$disabledString",
                                                        'data' => [
                                                            'toggle' => 'tooltip',
                                                            'original-title' => Yii::t('message', 'frontend.views.client.index.end', ['ru' => 'Завершить заказ']),
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
                    <?=
                    GridView::widget([
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
                        'resizableColumns' => false,
                    ]);
                    ?> 
                    <?php Pjax::end(); ?>   
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>    
    </div>
</section>
<?php
$vendorsText = strpos($user->email, '@delivery-club.ru') ? Yii::t('message', 'frontend.views.client.index.list', ['ru' => "Список ваших поставщиков. Специально для Вас мы добавили несколько рекомендованных нами поставщиков"]) : Yii::t('message', 'frontend.views.client.index.vendor_list', ['ru' => "Список ваших поставщиков."]);

$checkoutUrl = Url::to(['order/checkout']);
$createUrl = Url::to(['order/create']);
$requestUrl = Url::to(['request/list']);

$arr = [
    Yii::t('message', 'frontend.views.client.index.var', ['ru' => 'Да']),
    Yii::t('message', 'frontend.views.client.index.var1', ['ru' => 'Отмена']),
    Yii::t('message', 'frontend.views.client.index.var2', ['ru' => 'Создание заказа из прайс-листов ваших поставщиков.']),
    Yii::t('message', 'frontend.views.client.index.var3', ['ru' => 'Ваша корзина. Здесь хранятся заказы, готовые для отправки поставщику.']),
    Yii::t('message', 'frontend.views.client.index.var4', ['ru' => 'Вы всегда можете добавить поставщиков, с которыми уже работаете.']),
    Yii::t('message', 'frontend.views.client.index.var5', ['ru' => 'Или найти новых с помощью сервиса MixMarket.']),
    Yii::t('message', 'frontend.views.client.index.var6', ['ru' => 'Навигация по системе.']),
    Yii::t('message', 'frontend.views.client.index.var7', ['ru' => 'Вы всегда можете пройти обучение заново.']),
    Yii::t('message', 'frontend.views.client.index.var8', ['ru' => 'Завершить']),
    Yii::t('message', 'frontend.views.client.index.var9', ['ru' => 'Вперед']),
    Yii::t('message', 'frontend.views.client.index.var10', ['ru' => 'Назад']),
    Yii::t('message', 'frontend.views.client.index.var11', ['ru' => 'Начать работу']),
];

$customJs = <<< JS
    $(document).on('click','.dash-small-box', function(){
    var targetUrl = $(this).attr('data-target');
        if(targetUrl == 'checkout'){location.href = '$checkoutUrl';}
        if(targetUrl == 'order'){location.href = '$createUrl';}
        if(targetUrl == 'request'){location.href = '$requestUrl';}
        if(targetUrl == 'fmarket'){window.open('https://market.mixcart.ru');}
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
            confirmButtonText: "$arr[0]",
            cancelButtonText: "$arr[1]",
            showLoaderOnConfirm: true,
        }).then(function(result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else {
                document.location = clicked.data("url")
            }
        });
    });
JS;
$this->registerJs($customJs, View::POS_READY);

if (($organization->step == Organization::STEP_TUTORIAL) || ($organization->step == Organization::STEP_SET_INFO)) {
    $turnoffTutorial = Url::to(['/site/ajax-tutorial-off']);
    $customJs2 = <<< JS
    $(document).on('click','.dash-small-box', function(){
    var targetUrl = $(this).attr('data-target');
        if(targetUrl == 'checkout'){location.href = '$checkoutUrl';}
        if(targetUrl == 'order'){location.href = '$createUrl';}
        if(targetUrl == 'fmarket'){window.open('https://market.mixcart.ru');}
    }); 

                    var _slides = [{
                            title: '<img src="/images/welcome-client-bg.png" class="welcome-header-image" />',
                            content: '{$this->render("dashboard/welcome")}',
                            position: 'center',
                            overlayMode: 'all',
                            selector: 'html',
                            width: '450px',
                            height: '380px',
                    },
                    {
                            title: '&nbsp;',
                            content: '$arr[2]',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.step-order',
                    },
                    {
                            title: '&nbsp;',
                            content: '$arr[3]',
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
                            content: '$arr[4]',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.step-manage-vendors',
                    },
                    {
                            title: '&nbsp;',
                            content: '$arr[5]',
                            position: 'bottom-center',
                            overlayMode: 'focus',
                            selector: '.step-f-market',
                    },
                    {
                            title: '&nbsp;',
                            content: '$arr[6]',
                            position: 'right-center',
                            overlayMode: 'focus',
                            selector: '.sidebar',
                    },
                    {
                            title: '&nbsp;',
                            content: '$arr[7]',
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
                            labelEnd: '$arr[8]',
                            labelNext: '$arr[9]',
                            labelPrevious: '$arr[10]',
                            labelStart: '$arr[11]',
                            arrowPath: '/arrows/arrow-green.png',
                            fontSize: '14px',
                            onStop: function(currentSlideIndex, slideData, slideDom){
                                    $.get(
                                        '{$turnoffTutorial}'
                                    );
                                    $('#data-modal-wizard').trigger('invoke');
                                },
                    });

                    $.tutorialize.start();

JS;
    $this->registerJs($customJs2, View::POS_READY);
}
echo common\widgets\setinfo\SetInfoWidget::widget([
    'action' => '/site/ajax-complete-registration',
    'organization' => $organization,
    'profile' => $profile,
    'events' => 'invoke',
    'selector' => '#data-modal-wizard',
]);
?>
