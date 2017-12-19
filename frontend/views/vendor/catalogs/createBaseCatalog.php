<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\helpers\Json;
use common\models\Currency;
use yii\helpers\Url;

\frontend\assets\HandsOnTableAsset::register($this);

$currencySymbolListList = Currency::getSymbolList();
$firstCurrency = $currencySymbolListList[1];
$currencyList = Json::encode(Currency::getList());
$currencySymbolList = Json::encode($currencySymbolListList);

/* 
 * 
 */
$this->title = Yii::t('message', 'frontend.views.vendor.main_catalog_seven', ['ru'=>'Главный каталог']);
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
        'header' => '<h4>' . Yii::t('message', 'frontend.views.vendor.main_cat_down_two', ['ru'=>'Загрузка Главного каталога поставщика']) . ' </h4>',
        'footer' => '<a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> ' . Yii::t('message', 'frontend.views.vendor.close_three', ['ru'=>'Закрыть']) . ' </a>',
    ]);
    ?>
    <div class="modal-body form-inline"> 
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/ElzNEsKR0dA" frameborder="0" allowfullscreen=""></iframe>
        </div>
        <div style="padding-top: 15px;">
            <?= Yii::t('message', 'frontend.views.vendor.create_first', ['ru'=>'Для того, чтобы продолжить работу с нашей системой, создайте ваш первый каталог.']) ?>
        </div>
    </div>
    <?php
    Modal::end();
}
?>

<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.vendor.creating_of_main', ['ru'=>'Создание главного каталога']) ?>
        <small></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru'=>'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('message', 'frontend.views.vendor.new_cat_create', ['ru'=>'Создание главного каталога'])
        ],
    ])
    ?>
</section>

<section class="content">
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-danger alert-dismissable">
    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
    <h4><i class="icon fa fa-check"></i><?= Yii::t('error', 'frontend.views.vendor.error_two', ['ru'=>'Ошибка']) ?></h4>
    <?= Yii::$app->session->getFlash('success') ?>
    </div>
  <?php endif; ?>
<div class="box box-info">
    <div class="box-body">
        <div class="panel-body">
    <?= Html::a(
        '<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.save_six', ['ru'=>'Сохранить']) . ' ',
        ['#'],
        ['class' => 'btn btn-success pull-right','style' => ['margin-left'=>'5px'],'id'=>'save', 'name'=>'save']
    ) ?>
    <?= Html::a('<i class="glyphicon glyphicon-import"></i> <span class="text-label">' . Yii::t('message', 'frontend.views.vendor.downl_cat_two', ['ru'=>'Загрузить каталог (XLS)']) . ' </span>',
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
        '<i class="fa fa-list-alt"></i> ' . Yii::t('message', 'frontend.views.vendor.downl_templ_two', ['ru'=>'Скачать шаблон']) . ' ',
        Url::to('@web/upload/template.xlsx'),
        ['class' => 'btn btn-default pull-right','style' => ['margin'=>'0 5px;']]
    ) ?>
    <?= ''
//            Html::a('<i class="fa fa-question-circle" aria-hidden="true"></i>', ['#'], [
//                      'class' => 'btn btn-warning btn-sm pull-right',
//                      'style' => 'margin-right:10px;',
//                      'data' => [
//                      'target' => '#instruction',
//                      'toggle' => 'modal',
//                      'backdrop' => 'static',
//                         ],
//                      ]);
    ?>
            <?= 
                    Html::button('<span class="text-label">' . Yii::t('message', 'frontend.views.vendor.change_curr_three', ['ru'=>'Изменить валюту:']) . '  </span> <span class="currency-symbol">' . $firstCurrency . '</span>', [
                        'class' => 'btn btn-default pull-right',
                        'style' => ['margin'=>'0 5px;'],
                        'id' => 'changeCurrency',
                    ])
                    ?>
        </div>
        <div class="panel-body">
            <div class="handsontable" id="CreateCatalog"></div> 
        </div>
    </div>
</div>
</section>
<?php 
Modal::begin([
   'header'=>'<h4 class="modal-title">' . Yii::t('message', 'frontend.views.vendor.cat_down', ['ru'=>'Загрузка каталога']) . ' </h4>',
   'id'=>'instruction',
   'size'=>'modal-lg',
]);
echo '<iframe style="min-width: 320px;width: 100%;" width="854" height="480" id="video" src="https://www.youtube.com/embed/ElzNEsKR0dA" frameborder="0" allowfullscreen></iframe>';
Modal::end();
?>
<?php

$mped = \yii\helpers\ArrayHelper::getColumn(common\models\MpEd::find()->all(), 'name');
array_unshift($mped,"");
foreach ($mped as &$item){
    $item = Yii::t('app', $item);
}
$mped = json_encode($mped, JSON_UNESCAPED_UNICODE);

$supplierStartCatalogCreateUrl = \yii\helpers\Url::to(['vendor/supplier-start-catalog-create']);

$var1 = Yii::t('message', 'frontend.views.vendor.art_seven', ['ru'=>'Артикул']);
$var2 = Yii::t('message', 'frontend.views.vendor.product_three', ['ru'=>'Продукт']);
$var3 = Yii::t('message', 'frontend.views.vendor.multiplicity_four', ['ru'=>'Кратность']);
$var4 = Yii::t('message', 'frontend.views.vendor.price_six', ['ru'=>'Цена']);
$var5 = Yii::t('message', 'frontend.views.vendor.measure_four', ['ru'=>'Ед. измерения']);
$var6 = Yii::t('message', 'frontend.views.vendor.comment_two', ['ru'=>'Комментарий']);
$var7 = Yii::t('message', 'frontend.views.vendor.work', ['ru'=>'Приступить к работе']);
$var8 = Yii::t('message', 'frontend.views.vendor.ok', ['ru'=>'Окей!']);
$var9 = Yii::t('message', 'frontend.views.vendor.change_curr_four', ['ru'=>'Изменение валюты каталога']);
$var10 = Yii::t('message', 'frontend.views.vendor.choose_curr_two', ['ru'=>'Выберите новую валюту каталога']);
$var11 = Yii::t('message', 'frontend.views.vendor.choose_curr_from_list', ['ru'=>'Выберите валюту из списка']);
$var12 = Yii::t('message', 'frontend.views.vendor.curr_in_use', ['ru'=>'Данная валюта уже используется!']);
$var13 = Yii::t('message', 'frontend.views.vendor.curr_changed_two', ['ru'=>'Валюта каталога изменена!']);

$language = Yii::$app->sourceLanguage;

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
  colHeaders : ['$var1', '$var2', '$var3', '$var4 (<span class="currency-symbol">{$firstCurrency}</span>)', '$var5', '$var6'],
  colWidths: [40, 120, 45, 45, 65, 80],
  renderAllRows: true,
  columns: [
    {data: 'article'},
    {data: 'product', wordWrap:true},
    {
        data: 'units', 
        type: 'numeric',
        format: '0.00',
        language: '$language'
    },
    {
        data: 'price', 
        type: 'numeric',
        format: '0.00',
        language: '$language'
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
          data: $.param({'catalog':JSON.stringify(data), 'currency':currentCurrency}),
          cache: false,
          success: function (response) {
              if(response.success){ 
                bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "$var7",
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
                          label: "$var8",
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

    var currencies = $.map($currencySymbolList, function(el) { return el });
    var currentCurrency = 1;

    $(document).on("click", "#changeCurrency", function() {
        swal({
            title: '$var9',
            input: 'select',
            inputOptions: $currencyList,
            inputPlaceholder: '$var10',
            showCancelButton: true,
            allowOutsideClick: false,
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                    if (!value) {
                        reject('$var11')
                    }
                    if (value != currentCurrency) {
                        currentCurrency = value;
                        $(".currency-symbol").html(currencies[currentCurrency-1]);
                        resolve();
                    } else {
                        reject('$var12')
                    }
                })
            },
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else {
                swal({
                    title: '$var13',
                    type: 'success',
                    showCancelButton: false,
                })
            }
        })        
    });
        
JS;
$this->registerJs($customJs, View::POS_READY);
?>
