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
use yii\web\JsExpression;


?>
<?php 

// $productDesc = empty($model->product_rid) ? '' : $model->product->denom;


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
    СОПОСТАВЛЕНИЕ НОМЕНКЛАТУРЫ
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
        'product_id',
        [
           'attribute' => 'product_id',
           'value' => function ($model) {
                      return $model->fproductname->product;
                      },
           'format' => 'raw',
           'label' => 'Наименование F-keeper',                   
         ],
         [
           'attribute' => 'product_id',
           'value' => function ($model) {
                      return $model->fproductname->ed ? $model->fproductname->ed : 'Не указано';
                      },
           'format' => 'raw',
           'label' => 'Ед. изм. F-keeper',                   
         ],                     

     //   'munit_rid',

        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'pdenom',
               //       'value' => function ($model) {
               //       $model->pdenom = $model->product->denom;
               //       return $model->pdenom;
               //       },
            'label' => 'Наименование в Store House',
          //  'pageSummary' => 'Total',
            'vAlign' => 'middle',
            'width' => '210px',
            'refreshGrid' => true,
            'editableOptions'=>[
                 'formOptions' => ['action' => ['edit']],
        'header'=>'Продукт R-keeper', 
        'size'=>'md',
        'inputType'=>\kartik\editable\Editable::INPUT_SELECT2,
        //'widgetClass'=> 'kartik\datecontrol\DateControl',
        'options'=>[
          //   'initValueText' => $productDesc,

            //'data' => $pdenom,
            'options' => ['placeholder' => 'Выберите продукт из списка',           
                ],
             'pluginOptions' => [
                    'minimumInputLength' => 2,
                    'ajax' => [
                        'url' => Url::toRoute('autocomplete'),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {term:params.term}; }')
                    ],
                    'allowClear' => true
                ],
                'pluginEvents' => [
                    //"select2:select" => "function() { alert(1);}",
                    "select2:select" => "function() {
                        if($(this).val() == 0)
                        {
                            $('#agent-modal').modal('show');
                        }
                    }",
                    ]
            
        ]
        ]],
             /*   [
                'attribute' => 'product_rid',
                'value' => function ($model) {
                     if (!empty($model->product)) {
                         
                         return $model->product->denom;
                     }     
                          
                    return 'Не задано';
                },
                'format' => 'raw',
                'label' => 'Наименование StoreHouse', 
                ], */
                [
                'attribute' => 'munit_rid',
                'value' => function ($model) {
                    if (!empty($model->product)) {
                         
                         return $model->product->unitname;
                     }                   
                    return 'Не задано';
                },
                'format' => 'raw',
                'label' => 'Ед.изм. StoreHouse',         
                ],  
                [
                'attribute' => 'defquant',                
                'format' => 'raw',
                'label' => 'Кол-во в Заказе',                   
                ],           
                [
                'class'=>'kartik\grid\EditableColumn',
                'attribute'=>'koef',
                'refreshGrid' => true,
                'editableOptions'=>[
                'header'=>':<br><strong>1 единица F-keeper равна:&nbsp; &nbsp;</srong>',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXT,  
                'formOptions' => [
                              'action' => Url::toRoute('changekoef'),
                              'enableClientValidation' => false,
                                 ],    
                ],
                'hAlign'=>'right',
                'vAlign'=>'middle',
                // 'width'=>'100px',
                'format'=>['decimal',6],
                'pageSummary'=>true
                ],      
                [
                'class'=>'kartik\grid\EditableColumn',
                'attribute'=>'quant',
                'refreshGrid' => true,
                'editableOptions'=>[
                'header'=>':<br><strong>Новое количество равно:&nbsp; &nbsp;</srong>',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXT,  
                'formOptions' => [
                              'action' => Url::toRoute('changekoef')
                                 ],    
                ],
                'hAlign'=>'right',
                'vAlign'=>'middle',
                // 'width'=>'100px',
                'format'=>['decimal'],
                'pageSummary'=>true
                ],  
                [
                'class'=>'kartik\grid\EditableColumn',
                'attribute'=>'sum',
                'refreshGrid' => true,
                'editableOptions'=>[
                'header'=>'<strong>Новая сумма равна:&nbsp; &nbsp;</srong>',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXT,  
                'formOptions' => [
                              'action' => Url::toRoute('changekoef')
                                 ],    
                ],
                'hAlign'=>'right',
                'vAlign'=>'middle',
                // 'width'=>'100px',
                'format'=>['decimal',2],
                'pageSummary'=>true
                ], 
                 [
                'class'=>'kartik\grid\EditableColumn',
                'attribute'=>'vat',
                'value' => function ($model) {
                         return $model->vat/100;
                },
                'refreshGrid' => true,
                'editableOptions'=>[
                'header'=>'<strong>Новая ставка НДС равна:&nbsp; &nbsp;</srong>',
                'inputType'=>\kartik\editable\Editable::INPUT_DROPDOWN_LIST, 
                'data' => ['0' => '0', '1000' => '10', '1800' => '18'],    
                'formOptions' => [
                              'action' => Url::toRoute('changekoef')
                                 ],    
                ],
                'hAlign'=>'right',
                'vAlign'=>'middle',
                // 'width'=>'100px',
                'format'=>['decimal'],
                'pageSummary'=>true
                ],       
                
                [        
                'class' => 'yii\grid\ActionColumn',
                'contentOptions'=>['style'=>'width: 6%;'],
                'template'=>'{clear}&nbsp;',
                'visibleButtons' => [
                    'clear' => function ($model, $key, $index) {
                              // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                               return true;     
                                },                 
                                    ],
                'buttons'=>[                
                    'clear' =>  function ($url, $model) {
                              //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr\rkws\waybill\cleardata', 'id'=>$model->id]);
                                return \yii\helpers\Html::a( '<i class="fa fa-sign-in" aria-hidden="true"></i>', $customurl,
                                ['title' => Yii::t('backend', 'Вернуть начальные данные'), 'data-pjax'=>"0"]);
                                },                                                     
                            ]                               
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
                        <?= Html::a('Вернуться',
            ['index'],
            ['class' => 'btn btn-success btn-export']);
        ?>
                    </div>
                </div>    
            </div>
        </div>        
    </div>            
</section>

