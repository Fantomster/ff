<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use common\models\Order;
use yii\web\View;
use yii\widgets\ListView;
use kartik\grid\GridView;
use kartik\editable\Editable;
use api\common\models\RkAccess;
use api\common\models\RkWaybill;
use common\models\User;
use common\models\Organization;
use kartik\date\DatePicker;

?>

<?php
$organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id);
$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        $(".box-body").on("change", "#statusFilter", function() {
            $("#search-form").submit();
        });
        $(".box-body").on("change", "#orgFilter", function() {
            $("#search-form").submit();
        });
        $(".box-body").on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
    });
');
$this->registerCss("
    tr:hover{cursor: pointer;}
        ");
?>


<style>
.bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с R-keeper SH (White Server) 
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/vendorintegr'],
            ],
            'Интеграция с R-keeper WS',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    ЗАВЕРШЕННЫЕ ЗАКАЗЫ
</section>
<section class="content">
    <div class="catalog-index">

    	<div class="box box-info">            
            <div class="box-header with-border">
                            <div class="panel-body">
                                <div class ="box-body ">
                                    <?php
                                    Pjax::begin(['enablePushState' => false, 'id' => 'order-list',]);
                                    $form = ActiveForm::begin([
                                        'options' => [
                                            'data-pjax' => true,
                                            'id' => 'search-form',
                                            //'class' => "navbar-form",
                                            'role' => 'search',
                                        ],
                                        'enableClientValidation' => false,
                                        'method' => 'get',
                                    ]);
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-2 col-md-3 col-sm-6">
                                            <?=
                                            $form->field($searchModel, 'docStatus')
                                                ->dropDownList(['0' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.allstat', ['ru'=>'Все']), '1' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.nodoc', ['ru'=>'Не сформирована']), '2' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.ready', ['ru'=>'К выгрузке']), '3' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.completed', ['ru'=>'Выгружено']),
                                                //      '4' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.cancelled', ['ru'=>'Отменено'])
                                                ], ['id' => 'statusFilter'])
                                                ->label(Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.status', ['ru'=>'Статус накладной']), ['class' => 'label', 'style' => 'color:#555'])
                                            ?>
                                        </div>
                                        <div class="col-lg-2 col-md-3 col-sm-6">
                                            <?php
                                            if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                                                echo $form->field($searchModel, 'vendor_id')
                                                    ->dropDownList($organization->getSuppliers(), ['id' => 'orgFilter'])
                                                    ->label(Yii::t('message', 'frontend.views.order.vendors', ['ru'=>'Поставщики']), ['class' => 'label', 'style' => 'color:#555']);
                                            } else {
                                                echo $form->field($searchModel, 'client_id')
                                                    ->dropDownList($organization->getClients(), ['id' => 'orgFilter'])
                                                    ->label(Yii::t('message', 'frontend.views.order.rest', ['ru'=>'Рестораны']), ['class' => 'label', 'style' => 'color:#555']);
                                            }
                                            ?>
                                        </div>
                                        <div class="col-lg-5 col-md-6 col-sm-6">
                                            <?= Html::label(Yii::t('message', 'frontend.views.order.begin_end', ['ru'=>'Обновлено: Начальная дата / Конечная дата']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                                            <div class="form-group" style="width: 300px; height: 44px;">
                                                <?=
                                                DatePicker::widget([
                                                    'model' => $searchModel,
                                                    'attribute' => 'date_from',
                                                    'attribute2' => 'date_to',
                                                    'options' => ['placeholder' => Yii::t('message', 'frontend.views.order.date', ['ru'=>'Дата']), 'id' => 'dateFrom'],
                                                    'options2' => ['placeholder' => Yii::t('message', 'frontend.views.order.date_to', ['ru'=>'Конечная дата']), 'id' => 'dateTo'],
                                                    'separator' => '-',
                                                    'type' => DatePicker::TYPE_RANGE,
                                                    'pluginOptions' => [
                                                        'format' => 'dd.mm.yyyy', //'d M yyyy',//
                                                        'autoclose' => true,
                                                        'endDate' => "0d",
                                                    ]
                                                ])
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-5 col-md-6 col-sm-6">

                                        </div>
                                    </div>
                                    <?php ActiveForm::end(); ?>
                                    <?php if($organization->type_id == Organization::TYPE_SUPPLIER ){ ?>
                                        <?= Html::submitButton('<i class="fa fa-file-excel-o"></i> ' . Yii::t('app', 'frontend.views.order.index.report', ['ru'=>'отчет xls']), ['class' => 'btn btn-success export-to-xls']) ?>
                                    <?php }?>

                                    <?=
                                    GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'pjax' => true, // pjax is set to always true for this demo
                                    //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                                        'filterPosition' => false,
                                    //    'filterModel' => $searchModel,
                                        'columns' => [
                                                'id',
                                                [
                                                    'attribute' => 'vendor.name',
                                                    'value' => 'vendor.name',
                                                    'label' => 'Поставщик',
                                                    //'headerOptions' => ['class'=>'sorting',],
                                                ],
                                               /*
                                                [
                                                    'format' => 'raw',
                                                    'attribute' => 'status',
                                                    'value' => function($data) {
                                                                 $statusClass = 'done';
                                    
                                                    return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>';  
                                                               },
                                                     'label' => 'Статус Заказа',
                                                  ],
                                               */

                                                [
                                                    'attribute' => 'updated_at',
                                                    'label' => 'Обновлено',   
                                                    'format'=>'date',
                                                ],
                                                [
                                                    'format'=>'date',
                                                    'value' => function($data) {

                                                     $fdate = $data->actual_delivery ? $data->actual_delivery :
                                                            ( $data->requested_delivery ? $data->requested_delivery :
                                                              $data->updated_at);

                                                        return $fdate;
                                                    },
                                                    'label' => 'Финальная дата',
                                                ],
                                                [
                                                    'attribute' => 'positionCount',
                                                    'label' => 'Кол-во позиций',   
                                                    'format'=>'raw',
                                                ],                       
                                                [
                                                    'attribute' => 'total_price',
                                                    'label' => 'Итоговая сумма',   
                                                    'format'=>'raw',
                                                ],
                                                [
                                                    'value' => function($data) {
                                                                   
                                                     $nacl = RkWaybill::findOne(['order_id' => $data->id]); 
                                                     
                                                 //    var_dump($nacl->id);
                                                            if (isset($nacl->status)) {
                                                                return $nacl->status->denom;
                                                            }  else {
                                                                return 'Не сформирована';
                                                            }


                                                               },
                                                     'label' => 'Статус накладной', 
                                                ],                       
                                                [
                                                    'class'=>'kartik\grid\ExpandRowColumn',
                                                    'width'=>'50px',
                                                    'value'=>function ($model, $key, $index, $column) {
                                                                return GridView::ROW_COLLAPSED;
                                                             },
                                                    'detail'=>function ($model, $key, $index, $column) {
                                                              $wmodel = RkWaybill::find()->andWhere('order_id = :order_id',[':order_id'=> $model->id])->one();
                                                              
                                                              if ($wmodel) {
                                                                  $wmodel = RkWaybill::find()->andWhere('order_id = :order_id',[':order_id'=> $model->id]);
                                                              } else {
                                                                  $wmodel = null;
                                                              }
                                                              $order_id = $model->id;
                                                    return Yii::$app->controller->renderPartial('_expand-row-details', ['model'=>$wmodel,'order_id'=>$order_id]);
                                                              },
                                                    'headerOptions'=>['class'=>'kartik-sheet-style'], 
                                                    'expandOneOnly'=>true,
                                                ],
                                                ],
                                        /* 'rowOptions' => function ($data, $key, $index, $grid) {
                                          return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                                          }, */
                                        'options' => ['class' => 'table-responsive'],
                                        'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                        'bordered' => false,
                                        'striped' => true,
                                        'condensed' => false,
                                        'responsive' => false,
                                        'hover' => true,
                                        'resizableColumns' => false,
                                        'export' => [
                                            'fontAwesome' => true,
                                        ],
                                    ]);
                                    ?> 
                                </div>
                            </div>

                </div>
            </div>
        <?php Pjax::end() ?>
    </div>            
</section>

