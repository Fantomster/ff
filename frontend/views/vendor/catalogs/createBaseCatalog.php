<?php
use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use kartik\select2\Select2;
use common\models\Category;

\frontend\assets\HandsOnTableAsset::register($this);

/* 
 * 
 */
$this->title = 'Главный каталог';
$this->registerCss('
.Handsontable_table{position: relative;width: 100%;overflow: hidden;}
.hide{dosplay:none}
');
?>
<?php
Modal::begin([
    'id' => 'importFromXls',
    'clientOptions' => false,
    'size'=>'modal-lg',
    ]);
Modal::end();
?>
<?php
if (false) {
    $this->registerJs('
        $("document").ready(function(){
            $("#showVideo").modal("show");
            
            $("body").on("hidden.bs.modal", "#showVideo", function() {
                $("#showVideo").remove()
            });
        });
            ');

    Modal::begin([
        'id' => 'showVideo',
        'header' => '<h4>Загрузка Главного каталога поставщика</h4>',
        'footer' => '<a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> Закрыть</a>',
    ]);
    ?>
    <div class="modal-body form-inline"> 
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/ElzNEsKR0dA" frameborder="0" allowfullscreen=""></iframe>
        </div>
        <div style="padding-top: 15px;">
            Для того, чтобы продолжить работу с нашей системой, создайте ваш первый каталог.
        </div>
    </div>
    <?php
    Modal::end();
}
?>

<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> Создание главного каталога
        <small></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Создание главного каталога'
        ],
    ])
    ?>
</section>

<section class="content">
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-danger alert-dismissable">
    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
    <h4><i class="icon fa fa-check"></i>Ошибка</h4>
    <?= Yii::$app->session->getFlash('success') ?>
    </div>
  <?php endif; ?>
<div class="box box-info">
    <div class="box-body">
        <div class="panel-body">
    <?= Html::a(
        '<i class="icon fa fa-save"></i> Сохранить',
        ['#'],
        ['class' => 'btn btn-success pull-right','style' => ['margin-left'=>'5px'],'id'=>'save', 'name'=>'save']
    ) ?>
    <?= Html::a('<i class="glyphicon glyphicon-import"></i> <span class="text-label">Загрузить каталог (XLS)</span>', 
            ['/vendor/import-base-catalog-from-xls'], [
                'data' => [
                'target' => '#importFromXls',
                'toggle' => 'modal',
                'backdrop' => 'static',
                          ],
                'class'=>'btn btn-default pull-right',
                'style' => 'margin: 0 5px;',

            ]);
    ?>
    <?= Html::a(
        '<i class="fa fa-list-alt"></i> Скачать шаблон',
        Url::to('@web/upload/template.xlsx'),
        ['class' => 'btn btn-default pull-right','style' => ['margin'=>'0 5px']]
    ) ?>
    <?=Html::a('<i class="fa fa-question-circle" aria-hidden="true"></i>', ['#'], [
                      'class' => 'btn btn-warning btn-sm pull-right',
                      'style' => 'margin-right:10px;',
                      'data' => [
                      'target' => '#instruction',
                      'toggle' => 'modal',
                      'backdrop' => 'static',
                         ],
                      ]);?>
        </div>
        <div class="panel-body">
            <div class="handsontable" id="CreateCatalog"></div> 
        </div>
    </div>
</div>
</section>
<?php 
Modal::begin([
   'header'=>'<h4 class="modal-title">Загрузка каталога</h4>',
   'id'=>'instruction',
   'size'=>'modal-lg',
]);
echo '<iframe style="min-width: 320px;width: 100%;" width="854" height="480" id="video" src="https://www.youtube.com/embed/ElzNEsKR0dA" frameborder="0" allowfullscreen></iframe>';
Modal::end();
?>
<?php

$mped = \yii\helpers\ArrayHelper::getColumn(common\models\MpEd::find()->all(), 'name');
array_unshift($mped,"");
$mped = json_encode($mped, JSON_UNESCAPED_UNICODE);

$supplierStartCatalogCreateUrl = \yii\helpers\Url::to(['vendor/supplier-start-catalog-create']);

$customJs = <<< JS
var ed = $mped;
var arr = [];
var data = [];
        
for ( var i = 0; i < 60; i++ ) {
    data.push({article: '', product: '', units: '', price: '', ed: '', note: ''});
}
var container = document.getElementById('CreateCatalog');

height = $('.content-wrapper').height() - $("#CreateCatalog").offset().top - 20;
$(window).resize(function(){
        $("#CreateCatalog").height($('.content-wrapper').height() - $("#CreateCatalog").offset().top)
});
var save = document.getElementById('save'), hot;
       
hot = new Handsontable(container, {
  data: JSON.parse(JSON.stringify(data)),
  beforeChange: function () {
      //console.log('beforeChange');
  },
  colHeaders : ['Артикул', 'Продукт', 'Кратность', 'Цена (руб)', 'Ед. измерения', 'Комментарий'],
  colWidths: [40, 120, 45, 45, 65, 80],
  renderAllRows: true,
  columns: [
    {data: 'article'},
    {data: 'product', wordWrap:true},
    {
        data: 'units', 
        type: 'numeric',
        format: '0.00',
        language: 'ru-RU'
    },
    {
        data: 'price', 
        type: 'numeric',
        format: '0.00',
        language: 'ru-RU'
    },
    {
        data: 'ed', 
        type: 'dropdown',
        source: ed
    },
    {data: 'note'},   
    ],
  className : 'Handsontable_table',
  tableClassName: ['table-hover'],
  rowHeaders : true,
  stretchH : 'all',
  startRows: 1,
  autoWrapRow: true,
  height: height,
  });
Handsontable.Dom.addEvent(save, 'click', function() {
  var dataTable = hot.getData(),i, item, dataItem, data=[]; 
  var cleanedData = {};
  var cols = ['article', 'product', 'units', 'price', 'ed', 'note'];
    $.each(dataTable, function( rowKey, object) {
        if (!hot.isEmptyRow(rowKey)){
            cleanedData[rowKey] = object;
            dataItem = {};
            for(i = 0; i < cols.length; i+=1) {
              item = cleanedData[rowKey][i];
                dataItem[cols[i]] = item;
            }
            data.push({dataItem});
        }
    });
    //console.log(JSON.stringify(data));
    //return false;
    $.ajax({
          url: '$supplierStartCatalogCreateUrl',
          type: 'POST',
          dataType: "json",
          data: $.param({'catalog':JSON.stringify(data)}),
          cache: false,
          success: function (response) {
              if(response.success){ 
                bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "Приступить к работе",
                          className: "btn-success btn-md",
                          callback: function() {
                            location.reload();    
                          }
                        },
                    },
                    className: response.alert.class
                });
              }else{
                bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "Окей!",
                          className: "btn-success btn-md",
                        },
                    },
                    className: response.alert.class
                });
              }
          },
          error: function(response) {
          console.log(response.message);
          }
    });
})
$('#save').click(function(e){	
e.preventDefault();
});
var url = $("#video").attr('src');        
$("#instruction").on('hide.bs.modal', function(){
$("#video").attr('src', '');
});
$("#instruction").on('show.bs.modal', function(){
$("#video").attr('src', url);
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>
