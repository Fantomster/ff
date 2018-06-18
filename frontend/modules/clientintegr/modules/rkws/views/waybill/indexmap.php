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
use kartik\checkbox\CheckboxX;
use api\common\models\RkAccess;
use api\common\models\RkWaybill;
use yii\web\JsExpression;
use api\common\models\RkDicconst;


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
<?php $useAutoVAT            = (RkDicconst::findOne(['denom' => 'useTaxVat'])->getPconstValue() != null) ? RkDicconst::findOne(['denom' => 'useTaxVat'])->getPconstValue() : 1; ?>
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
                        <div style="text-align:center;"> 
                    <?php echo '<label class="cbx-label" for="s_1">Цены в Заказе включают НДС</label>';
                          echo CheckboxX::widget([
                                'name'=>'s_1',
                                'value' => $wmodel->vat_included ? 1 : 0,
                                'options'=>['id'=>'s_1'],
                                'pluginOptions'=>['threeState'=>false],
                                'pluginEvents' =>[ 'change'=>'function() {
                                    
                                  //  var output = $("#output"); // блок вывода информации
                                    
                                    $.ajax({
                                                url: "changevat", // путь к php-обработчику
                                                type: "POST", // метод передачи данных
                                            // dataType: "json", // тип ожидаемых данных в ответе
                                                data: {key: this.value + "," + "'.$wmodel->id.'"}, // данные, которые передаем на сервер
                                            /*            
                                          beforeSend: function(){ // Функция вызывается перед отправкой запроса
                                                      output.text("Запрос отправлен. Ждите ответа.");
                                                                }, */
                                                /*                    
                                               error: function(req, text, error){ // отслеживание ошибок во время выполнения ajax-запроса
                                                      output.text("Хьюстон, У нас проблемы! " + text + " | " + error);
                                                                }, */
                                                /*                    
                                            complete: function(){ // функция вызывается по окончании запроса
                                                      output.append("<p>Запрос полностью завершен!</p>");
                                                                }, */
                                                                
                                              success: function(json){ // функция, которая будет вызвана в случае удачного завершения запроса к серверу
                                                      // json - переменная, содержащая данные ответа от сервера. Обзывайте её как угодно ;)
                                                      // output.html(json); // выводим на страницу данные, полученные с сервера
                                                      // $("map_grid1").refresh;
                                                      $.pjax.reload({container:"#map_grid1"}); 
                                                                    }
                                            }); 
                                            // alert(this.value);
                                            }'],
                                ]); ?>    
                        </div>


<?php
$columns = array(
    'product_id',
    [
        'attribute' => 'fproductname',
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
            'asPopover' => $isAndroid ? false : true,
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
            'asPopover' => $isAndroid ? false : true,
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
            'asPopover' => $isAndroid ? false : true,
            'header'=>':<br><strong>Новое количество равно:&nbsp; &nbsp;</srong>',
            'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
            'formOptions' => [
                'action' => Url::toRoute('changekoef'),
                'enableClientValidation' => false,
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
            'asPopover' => $isAndroid ? false : true,
            'header'=>'<strong>Новая сумма равна:&nbsp; &nbsp;</srong>',
            'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
            'formOptions' => [
                'action' => Url::toRoute('changekoef'),
                'enableClientValidation' => false,
            ],
        ],
        'hAlign'=>'right',
        'vAlign'=>'middle',
        // 'width'=>'100px',
        'format'=>['decimal',2],

        'pageSummary'=>true
    ]);



    array_push($columns,
        [
            'attribute' => 'vat',
            'format' => 'raw',
            'label' => 'НДС',
            'contentOptions' => ['class' => 'text-right'],
            'value' => function($model) {
                return $model->vat/100; }
        ]);

    /*   [
        'attribute' => 'vat',
        'format' => 'raw',
        'label' => 'Ставка НДС',
        'contentOptions' => ['class' => 'text-right'],
        'value' => function($model) {
           $exportVAT = RkDicconst::findOne(['denom' => 'taxVat'])->getPconstValue()/100;
           return $exportVAT;
        }
       ], */

       $sLinkzero = Url::base(true).Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/makevat', 'waybill_id' => $wmodel->id,'vat' =>0]);
       $sLinkten = Url::base(true).Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/makevat', 'waybill_id' => $wmodel->id,'vat' =>1000]);
       $sLinkeight = Url::base(true).Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/makevat', 'waybill_id' => $wmodel->id,'vat' =>1800]);

array_push($columns,
    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 6%;'],
        'template'=>'{zero}&nbsp;{ten}&nbsp;{eighteen}',
        // 'header' => '<a class="label label-default" href="setvatz">0</a><a class="label label-default" href="setvatt">10</a><a class="label label-default" href="setvate">18</a>',
        'header' => '<span align="center"> <button id="btnZero" type="button" onClick="location.href=\''.$sLinkzero.'\';" class="btn btn-xs btn-link" style="color:green;">0</button>'.
                    '<button id="btnTen" type="button" onClick="location.href=\''.$sLinkten.'\';" class="btn btn-xs btn-link" style="color:green;">10</button>'.
                    '<button id="btnEight" type="button" onClick="location.href=\''.$sLinkeight.'\';" class="btn btn-xs btn-link" style="color:green;">18</button></span>',

      //  'sort' => false,
      //  '' => false,

        'visibleButtons' => [
            'zero' => function ($model, $key, $index) {
                // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                return true;
            },
        ],
        'buttons'=>[
            'zero' =>  function ($url, $model) {

                if ($model->vat == 0) {
                    $tClass = "label label-success";
                    $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";

                } else {
                    $tClass = "label label-default";
                    $tStyle = "";
                }

                //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/chvat', 'id'=>$model->id, 'vat' =>0]);
                return \yii\helpers\Html::a( '&nbsp;0', $customurl,
                    ['title' => Yii::t('backend', '0%'), 'data-pjax'=>"0", 'class'=> $tClass, 'style'=>$tStyle]);
            },
            'ten' =>  function ($url, $model) {

                if ($model->vat == 1000) {
                    $tClass = "label label-success";
                    $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                } else {
                    $tClass = "label label-default";
                    $tStyle = "";
                }

                //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/chvat', 'id'=>$model->id, 'vat' => '1000']);
                return \yii\helpers\Html::a( '10', $customurl,
                    ['title' => Yii::t('backend', '10%'), 'data-pjax'=>"0", 'class'=> $tClass, 'style'=>$tStyle]);
            },
            'eighteen' =>  function ($url, $model) {

                if ($model->vat == 1800) {
                    $tClass = "label label-success";
                    $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                } else {
                    $tClass = "label label-default";
                    $tStyle = "";
                }

                //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/chvat', 'id'=>$model->id, 'vat' => '1800']);
                return \yii\helpers\Html::a( '18', $customurl,
                    ['title' => Yii::t('backend', '18%'), 'data-pjax'=>"0", 'class'=> $tClass, 'style'=>$tStyle]);
            },
        ]
    ]);

array_push($columns,
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
                $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/cleardata', 'id'=>$model->id]);
                return \yii\helpers\Html::a( '<i class="fa fa-sign-in" aria-hidden="true"></i>', $customurl,
                    ['title' => Yii::t('backend', 'Вернуть начальные данные'), 'data-pjax'=>"0"]);
            },
        ]
    ]);
?>
                        <?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'pjax' => true, // pjax is set to always true for this demo
    'pjaxSettings' => ['options' => ['id' => 'map_grid1']],
    'filterPosition' => false,
    'columns' => $columns,
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
            [$this->context->getLastUrl().'way='.$wmodel->order_id],
            ['class' => 'btn btn-success btn-export']);
        ?>
                    </div>
                </div>    
            </div>
        </div>        
    </div>            
</section>

<?php 
/*
$js = "
  $('#s_1').on('change', function(){
  
    alert($('#s_1').checked);
    return true;
  /*
    $.ajax({
      url: 'changevat', // путь к php-обработчику
      type: 'POST', // метод передачи данных
      dataType: 'json', // тип ожидаемых данных в ответе
      data: {key: 1}, // данные, которые передаем на сервер
      beforeSend: function(){ // Функция вызывается перед отправкой запроса
        output.text('Запрос отправлен. Ждите ответа.');
      },
      error: function(req, text, error){ // отслеживание ошибок во время выполнения ajax-запроса
        output.text('Хьюстон, У нас проблемы! ' + text + ' | ' + error);
      },
      complete: function(){ // функция вызывается по окончании запроса
        output.append('<p>Запрос полностью завершен!</p>');
      },
      success: function(json){ // функция, которая будет вызвана в случае удачного завершения запроса к серверу
        // json - переменная, содержащая данные ответа от сервера. Обзывайте её как угодно ;)
        output.html(json); // выводим на страницу данные, полученные с сервера
      }
    });

  });

";
    
$this->registerJs($js);  
*/
?>

