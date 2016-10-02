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

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Редактирование каталога <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?></h3>
        <?= Html::a(
                'Сохранить',
                ['#'],
                ['class' => 'btn btn-sm btn-success pull-right','id'=>'save', 'name'=>'save']
        ) ?>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <?='<li>'.Html::a('Название',['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a('Добавить товары',['vendor/step-2','id'=>$cat_id]).'</li>'?>
                <?='<li class="active">'.Html::a('Изменить цены',['vendor/step-3-copy','id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a('Назначить',['vendor/step-4','id'=>$cat_id]).'</li>'?>
            </ul>
        </div>
        <div class="panel-body">
            <?php Pjax::begin(['id' => 'pjax-container']); ?>
                <div class="handsontable" id="handsontable"></div> 
            <?php Pjax::end(); ?>   
        </div>
    </div>
</div>
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
var colsToHide = [0];
/*function getCustomRenderer() {
    return function(instance, td, row, col, prop, value, cellProperties) {
        console.log(td)
      Handsontable.renderers.TextRenderer.apply(this, arguments);
      if (colsToHide.indexOf(col) > -1) {
        td.hidden = true;
      } else {
        td.hidden = false;
      }
    }
  }*/
  hot = new Handsontable(container, {
  data: JSON.parse(JSON.stringify(data)),
  colHeaders : ['id','Артикул', 'Наименование', 'Базовая цена', 'Цена каталога','Скидка в рублях','Скидка %','Итоговая цена'],
  colWidths: [50,50, 90, 50, 50, 50, 50, 50],
  renderAllRows: true,
  maxRows: $arr_count,
   fillHandle: false,
   minSpareCols: 0,
   minSpareRows: 0,
  rowHeaders: true,
  hiddenColumns: {
      columns: [0]
    },
  columns: [
    {data: 'goods_id',readOnly: true},
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
        format: '0.00 $',
        language: 'ru-RU'
    }, 
    {
        data: 'discount',
        type: 'numeric',
        format: '0.00 $',
        language: 'ru-RU'
    },
    {data: 'discount_percent', type: 'numeric',format: '0',},
    {data: 'total_price',readOnly: true,type: 'numeric',format: '0.00',language: 'ru-RU'},
  ],
  className : 'Handsontable_table',
  tableClassName: ['table-hover'],
  rowHeaders : true,
  stretchH : 'all',
  startRows: 1,
  autoWrapRow: true,
  height: height,
  //renderer: getCustomRenderer(),
  beforeChangeRender: function (changes, source) {
      if(source !== 'sum'){
          var a, b, c, sum, i, value;
            var change = changes[0];
            var line = change[0];
            a = parseFloat(this.getDataAtCell(line, 4));
            b = parseFloat(this.getDataAtCell(line, 5));
            c = parseInt(this.getDataAtCell(line, 6));
            if(c>100)c=100;
            if(c<-100)c=-100;
            if(changes[0][1]=='price'){ 
            this.setDataAtCell(change[0], 5, '0,00', 'sum');
            this.setDataAtCell(change[0], 6, '0', 'sum');
            this.setDataAtCell(change[0], 7, a, 'sum');
            }
            if(changes[0][1]=='discount'){ 
            this.setDataAtCell(change[0], 6, 0, 'sum');
            value = a - b;
            this.setDataAtCell(change[0], 7, value, 'sum');
            }
            if(changes[0][1]=='discount_percent'){
            this.setDataAtCell(change[0], 5, '0,00', 'sum');
            this.setDataAtCell(change[0], 6, c, 'sum');
            valueTwo = a - (a/100 * c);
            this.setDataAtCell(change[0], 7, valueTwo, 'sum');   
            }
        }      
      }
  });

Handsontable.Dom.addEvent(save, 'click', function() {
  var dataTable = hot.getData(),i, item, dataItem, data=[]; 
        //console.log(hot.getData())
        //return false;
  var cleanedData = {};
  var cols = ['goods_id',2, 3, 4, 5,6,7,'total_price'];
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
    $.ajax({
          url: "index.php?r=vendor/step-3-copy&id=$cat_id",
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
                          label: "Успешно!",
                          className: "btn-success btn-md",
                          callback: function() {
                            //location.reload(); 
                              $.pjax.reload({container: "#pjax-container"});
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
});
$('#save').click(function(e){	
e.preventDefault();
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>
