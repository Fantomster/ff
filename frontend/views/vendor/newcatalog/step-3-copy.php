<?php
use yii\widgets\Breadcrumbs;
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
\frontend\assets\HandsOnTableAsset::register($this);

$this->registerCss('.handsontable .htCore .htDimmed {
   background-color: #ececec !important;
    cursor: not-allowed;
    color: #696969;
 }.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}.Handsontable_table{position: relative;width: 100%;overflow: hidden;height:400px;}');
$this->title = 'Редактировать продукты';

?>
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> Редактирование каталога <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?>
        <small></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
            'label' => 'Каталоги',
            'url' => ['vendor/catalogs'],
            ],
            'Шаг 3. Редактирование каталога',
        ],
    ])
    ?>
</section>
<section class="content">
<div class="box box-info">
    <!-- /.box-header -->
    <div class="box-body">
        <div class="panel-body">
            <ul class="nav fk-tab nav-tabs pull-left">
                <?='<li>'.Html::a('Название',['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a('Добавить товары',['vendor/step-2','id'=>$cat_id]).'</li>'?>
                <?='<li class="active">'.Html::a('Изменить цены <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-3-copy','id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a('Назначить ресторану',['vendor/step-4','id'=>$cat_id]).'</li>'?>
            </ul>
            <ul class="fk-prev-next pull-right">
              <?='<li class="fk-prev">'.Html::a('Назад',['vendor/step-2','id'=>$cat_id]).'</li>'?>
              <?='<li class="fk-next">'.Html::button('<span><i class="fa fa-save"></i> Далее</span>', [
                  'id'=>'save', 
                  'name'=>'save',
                  'data' => [
                      'url' => Url::to(['vendor/step-4','id'=>$cat_id]),
                      'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Сохраняем...",
                  ],
                  ]).'</li>'?>
            </ul>
        </div>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4>ШАГ 3</h4>
                <p>Отлично. Теперь осталось установить цены на товары в новом каталоге.<br>Это можно сделать задав фиксированную скидку, процент скидки или просто указав новую цену.</p>
            </div> 
            <div class="row">
                <div class="col-sm-4">
                    <div class="input-group">
                            <span class="input-group-addon">
                              <i class="fa fa-search"></i>
                            </span>
                    <?=Html::input('text', 'search_field', null, ['class' => 'form-control','placeholder'=>'Поиск','id'=>'search_field']) ?>
                    </div>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <?php /*=Html::a('<i class="fa fa-pencil m-r-xs"></i> установить скидку на весь ассортимент', 
                    [
                    'vendor/ajax-set-percent','id'=>$cat_id
                    ], 
                    [
                    'data' => [
                        'target' => '#discount-all-product',
                        'toggle' => 'modal',
                        'backdrop' => 'static',
                        ],'class'=>'pull-left'
                    ])*/?>
            <?php Pjax::begin(['id' => 'pjax-container']); ?>
                <div class="handsontable" id="handsontable"></div> 
            <?php Pjax::end(); ?>   
        </div>
    </div>
</div>
</section>
<?=Modal::widget([
'id' => 'discount-all-product',
'clientOptions' => false,
])
?>
<?php
$arr= json_encode($array, JSON_UNESCAPED_UNICODE);
$arr_count = count($array);

$step3CopyUrl = Url::to(['vendor/step-3-copy', 'id' => $cat_id]);
$step4Url = Url::to(['vendor/step-4', 'id' => $cat_id]);

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
var searchFiled = document.getElementById('search_field');        
height = $('.content-wrapper').height() - $("#handsontable").offset().top;
$(window).resize(function(){
        $("#handsontable").height($('.content-wrapper').height() - $("#handsontable").offset().top)
});
var save = document.getElementById('save'), hot, originalColWidths = [], colWidths = [];         
  hot = new Handsontable(container, {
  data: JSON.parse(JSON.stringify(data)),
  //clickBeginsEditing : true,
  colHeaders : ['Артикул','id', 'Наименование', 'Базовая цена', 'Индивидуальная цена', 'Ед. измерения','Скидка в рублях','Скидка %','Итоговая цена'],
  search: true,
  renderAllRows: false,
  maxRows: $arr_count,
   fillHandle: false,
   minSpareCols: 0,
   minSpareRows: 0,
  rowHeaders: true,
  columns: [
    {data: 'article',readOnly: true},
    {data: 'goods_id',readOnly: true},
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
    {data: 'ed',readOnly: true}, 
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
  rowHeaders : true,
  stretchH : 'all',
  startRows: 1,
  autoWrapRow: true,
  height: height,
  /*afterOnCellMouseDown: function (changes, source) {
    afterBeginEditing();
 },*/
  beforeChangeRender: function (changes, source) {
      if(source !== 'sum'){
          var a, b, c, sum, i, value;
            var change = changes[0];
            var line = change[0];
            a = parseFloat(this.getDataAtCell(line, 4));
            b = parseFloat(this.getDataAtCell(line, 6));
            c = parseInt(this.getDataAtCell(line, 7));
            if(c>100)c=100;
            if(c<-100)c=-100;
            if(changes[0][1]=='price'){ 
            this.setDataAtCell(change[0], 6, '0,00', 'sum');
            this.setDataAtCell(change[0], 7, '0', 'sum');
            this.setDataAtCell(change[0], 8, a, 'sum');
            }
            if(changes[0][1]=='discount'){ 
            this.setDataAtCell(change[0], 7, 0, 'sum');
            value = a - b;
            this.setDataAtCell(change[0], 8, value, 'sum');
            }
            if(changes[0][1]=='discount_percent'){
            this.setDataAtCell(change[0], 6, '0,00', 'sum');
            this.setDataAtCell(change[0], 7, c, 'sum');
            valueTwo = a - (a/100 * c);
            this.setDataAtCell(change[0], 8, valueTwo, 'sum');   
            }
        }      
      }
  });
colWidths[1] = 0.1;
hot.updateSettings({colWidths: colWidths});
function getRowsFromObjects(queryResult) {
    rows = [];
    for (var i = 0, l = queryResult.length; i < l; i++) {
      //                        debugger
      rows.push(queryResult[i].row);

    }
    console.log('rows', rows);
    return rows;
  }

  Handsontable.Dom.addEvent(searchFiled, 'keyup', function(event) {
    //                    debugger
    hot.loadData(data);

    var queryResult = hot.search.query(this.value);
    rows = getRowsFromObjects(queryResult);
    var filtered = data.filter(function(_, index) {
      return !searchFiled.value || rows.indexOf(index) >= 0;
    });

    hot.loadData(filtered);
    hot.render();
  });        
Handsontable.Dom.addEvent(save, 'click', function() {
  var dataTable = hot.getData(),i, item, dataItem, data=[]; 
  var cleanedData = {};
  var cols = [1,'goods_id',3, 4, 5,6,7,8,'total_price'];
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
    $('#save').button("loading");
    $.ajax({
          url: "$step3CopyUrl",
          type: 'POST',
          dataType: "json",
          data: $.param({'catalog':JSON.stringify(data)}),
          cache: false,
          success: function (response) {
              if(response.success){ 
                var url = "$step4Url";
                $(location).attr("href",url);
              }else{
                $('#save').button("reset");
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
$(".set").live("click", function() {
var form = $("#set_discount_percent");
$.post(
    form.attr("action"),
        form.serialize()
    ).done(function(result) {
        form.replaceWith(result);
    });
return false;
})
JS;
$this->registerJs($customJs, View::POS_READY);
?>
