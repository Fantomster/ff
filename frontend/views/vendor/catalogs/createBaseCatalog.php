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
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 3 кнопки [Импорт каталога формата xls / Скачать шаблон][Создать каталог]
 * 1) Импорт - по аналогии скачать функционал с basecatalog
 * 2) Создать каталог - по аналогии с рестораном (графический Эксель)
 * 
 * 2 ations  
 * 1) importBaseCatalog
 * 2) createBaseCatalogStep1
 * 
 */
$this->registerCss('
.Handsontable_table{position: relative;width: 100%;overflow: hidden;}
.hide{dosplay:none}
');
$this->registerCssFile('modules/handsontable/dist/handsontable.full.css');
$this->registerCssFile('modules/handsontable/dist/bootstrap.css');
$this->registerCssFile('modules/handsontable/dist/chosen.css');
$this->registerCssFile('modules/handsontable/dist/pikaday/pikaday.css');
$this->registerjsFile('modules/handsontable/dist/pikaday/pikaday.js');
$this->registerjsFile('modules/handsontable/dist/moment/moment.js');
$this->registerjsFile('modules/handsontable/dist/numbro/numbro.js');
$this->registerjsFile('modules/handsontable/dist/zeroclipboard/ZeroClipboard.js');
$this->registerjsFile('modules/handsontable/dist/numbro/languages.js');
$this->registerJsFile('modules/handsontable/dist/handsontable.js');
$this->registerJsFile('modules/handsontable/dist/handsontable-chosen-editor.js');
$this->registerJsFile(Yii::$app->request->BaseUrl . '/modules/handsontable/dist/chosen.jquery.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
<?php
if (isset($step) && ($step == common\models\Organization::STEP_ADD_CATALOG)) {
    echo yii\bootstrap\Alert::widget([
        'options' => [
            'class' => 'alert-warning fade in',
        ],
        'body' => 'Для того, чтобы продолжить работу с нашей системой, создайте ваш первый каталог. '
        . '<a class="btn btn-default btn-sm" href="#">Сделаем это!</a>',
    ]);
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
<div class="box box-info">
    <div class="box-body">
        <div class="panel-body">
    <?= Html::a(
        '<i class="icon fa fa-save"></i> Сохранить',
        ['#'],
        ['class' => 'btn btn-success pull-right','style' => ['margin-left'=>'5px'],'id'=>'save', 'name'=>'save']
    ) ?>
    <?=
        Modal::widget([
            'id' => 'importToXls',
            'clientOptions' => false,
            'size'=>'modal-md',
            'toggleButton' => [
                'label' => '<i class="glyphicon glyphicon-import"></i> Загрузить каталог',
                'tag' => 'a',
                'data-target' => '#importToXls',
                'class' => 'btn btn-default pull-right',
                'href' => Url::to(['/vendor/import-base-catalog-from-xls']),
                'style' => 'margin: 0 5px;',
            ],
        ])
    ?>
    <?= Html::a(
        '<i class="fa fa-list-alt"></i> Скачать шаблон',
        Url::to('@web/upload/template.xlsx'),
        ['class' => 'btn btn-default pull-right','style' => ['margin'=>'0 5px']]
    ) ?>
    <?php /* 
        Modal::widget([
            'id' => 'info',
            'clientOptions' => false,
            'size'=>'modal-md',
            'toggleButton' => [
                'label' => '<i class="fa fa-question-circle" aria-hidden="true"></i> Инструкция',
                'tag' => 'a',
                'data-target' => '#info',
                'class' => 'btn btn-default pull-right',
                'href' => Url::to(['#']),
                'style' => 'margin-right:5px;',
            ],
        ]) */
    ?>
        </div>
        <div class="panel-body">
            <div class="handsontable" id="CreateCatalog"></div> 
        </div>
    </div>
</div>
</section>
<?php
//$categorys = json_encode(common\models\Category::allCategory(), JSON_UNESCAPED_UNICODE);
$catgrs = \yii\helpers\ArrayHelper::getColumn(common\models\Category::find()->all(), 'name');
array_unshift($catgrs,"");
$catgrs = json_encode($catgrs, JSON_UNESCAPED_UNICODE);

$customJs = <<< JS
var category = $catgrs;
var arr = [];
var data = [];
        
for ( var i = 0; i < 60; i++ ) {
    data.push({article: '', product: '', units: '', price: '', ed: '', category: '', note: ''});
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
  colHeaders : ['Артикул', 'Продукт', 'Кратность', 'Цена (руб)', 'Ед. измерения', 'Категория', 'Комментарий'],
  colWidths: [40, 120, 45, 45, 65,  55,  80],
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
    {data: 'ed'},
    {
        data: 'category', 
        type: 'dropdown',
        source: category
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
  var cols = ['article', 'product', 'units', 'price', 'ed', 'category', 'note'];
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
          url: 'index.php?r=vendor/supplier-start-catalog-create',
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
JS;
$this->registerJs($customJs, View::POS_READY);
?>
