<?php
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Users;
use kartik\export\ExportMenu;
use kartik\editable\Editable;
$this->registerCss('.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}.Handsontable_table{position: relative;width: 100%;overflow: hidden;height:400px;}');
$this->title = 'Редактировать продукты';

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

<div class="panel-body">
    <h3 class="font-light"><i class="fa fa-list-alt"></i> Редактирование каталога <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?></h3>
</div>
<div class="panel-body">
    <ul class="nav nav-tabs">
        <?='<li>'.Html::a('Имя каталога',['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
        <?='<li>'.Html::a('Добавить продукты',['vendor/step-2','id'=>$cat_id]).'</li>'?>
        <?='<li class="active">'.Html::a('Редактировать',['vendor/step-3','id'=>$cat_id]).'</li>'?>
        <?='<li>'.Html::a('Назначить',['vendor/step-4','id'=>$cat_id]).'</li>'?>
    </ul>
</div>
<div class="panel-body">
<?= Html::a(
        'Сохранить',
        ['#'],
        ['class' => 'btn btn-success pull-right','style' => ['margin-left'=>'5px','margin-bottom'=>'15px'],'id'=>'save', 'name'=>'save']
) ?>
<div class="handsontable" id="handsontable"></div>    
    
    
    
    
</div>
<?php Pjax::begin(['id' => 'pjax-container']); ?>

<?php Pjax::end(); ?>
<?php
$arr= json_encode($array, JSON_UNESCAPED_UNICODE);
$arr_count = count($array);
$customJs = <<< JS
/** 
 * Forward port jQuery.live()
 * Wrapper for newer jQuery.on()
 * Uses optimized selector context 
 * Only add if live() not already existing.
*/
if (typeof jQuery.fn.live == "undefined" || !(jQuery.isFunction(jQuery.fn.live))) {
  jQuery.fn.extend({
      live: function (event, callback) {
         if (this.selector) {
              jQuery(document).on(event, this.selector, callback);
          }
      }
  });
}      
var data = $arr;
var container = document.getElementById('handsontable');
height = $('.content-wrapper').height() - $("#handsontable").offset().top;
$(window).resize(function(){
        $("#handsontable").height($('.content-wrapper').height() - $("#handsontable").offset().top)
});
var save = document.getElementById('save'), hot;      
hot = new Handsontable(container, {
  data: JSON.parse(JSON.stringify(data)),
  colHeaders : ['Артикул', 'Наименование', 'Базовая цена', 'Цена каталога','Скидка в рублях','Скидка %','Итоговая цена'],
  colWidths: [50, 90, 50, 50, 50, 50, 50],
  renderAllRows: true,
  maxRows: $arr_count,
  columns: [
    {data: 'article',readOnly: true},
    {data: 'product', wordWrap:true,readOnly: true},  
    {
        data: 'base_price', 
        type: 'numeric',
        format: '0.00',
        language: 'ru-RU',
        readOnly: true
    },
    {
        data: 'price', 
        type: 'numeric',
        format: '0.00',
        language: 'ru-RU'
    }, 
    {
        data: 'discount',
        type: 'numeric',
        format: '0.00',
        language: 'ru-RU'
    },
    {data: 'discount_percent'},
    {data: 'total_price',readOnly: true},
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
  var cols = ['article', 'product', 'units', 'price', 'category'];
    $.each(dataTable, function( rowKey, object) {
        
    })
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>
