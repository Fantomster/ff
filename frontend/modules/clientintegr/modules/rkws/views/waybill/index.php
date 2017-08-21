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
                                <div class="box-body table-responsive no-padding">
                                    <?=
                                    GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'pjax' => true, // pjax is set to always true for this demo
                                    //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                                        'filterPosition' => false,
                                        'columns' => [
                                                'id',
                                                [
                                                    'attribute' => 'vendor.name',
                                                    'value' => 'vendor.name',
                                                    'label' => 'Поставщик',
                                                    //'headerOptions' => ['class'=>'sorting',],
                                                ],
                                                [
                                                    'format' => 'raw',
                                                    'attribute' => 'status',
                                                    'value' => function($data) {
                                                                 $statusClass = 'done';
                                    
                                                    return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>';  
                                                               },
                                                     'label' => 'Статус Заказа',
                                                  ],
                                                [
                                                    'attribute' => 'updated_at',
                                                    'label' => 'Обновлено',   
                                                    'format'=>'date',
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
                                                             return $nacl ? $nacl->status->denom : 'Не сформирована';  
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
    </div>            
</section>

