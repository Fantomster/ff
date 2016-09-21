<?php
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
.Handsontable_table{position: relative;width: 100%;height:500px;overflow: hidden;}
.modal-title {color: #69b3e3;text-align: center;font-size: 16px;font-weight: 900;}
.font-light {font-weight: 300;}
.hide{dosplay:none}
.modal-footer {border-top:1px solid #ccc;background-color: #ecf0f5}
');
$this->registerCssFile('modules/handsontable/dist/handsontable.full.css');
$this->registerCssFile('modules/handsontable/dist/pikaday/pikaday.css');
$this->registerjsFile('modules/handsontable/dist/pikaday/pikaday.js');
$this->registerjsFile('modules/handsontable/dist/moment/moment.js');
$this->registerjsFile('modules/handsontable/dist/numbro/numbro.js');
$this->registerjsFile('modules/handsontable/dist/zeroclipboard/ZeroClipboard.js');
$this->registerjsFile('modules/handsontable/dist/numbro/languages.js');
$this->registerJsFile('modules/handsontable/dist/handsontable.js');
?>
<div class="panel-body">   
    <h3 class="font-light"><i class="fa fa-list-alt"></i> Создание главного каталога</h3>
</div>
<div class="panel-body"> 
<?= Html::a(
        'Сохранить',
        ['#'],
        ['class' => 'btn btn-success pull-right save']
    ) ?>
<div class="btn-group m-t-xs m-r pull-right" placement="left" style="margin-right: 10px">
    <?=
        Modal::widget([
            'id' => 'importToXls',
            'clientOptions' => false,
            'size'=>'modal-md',
            'toggleButton' => [
                'label' => '<i class="glyphicon glyphicon-import"></i> Импорт',
                'tag' => 'a',
                'data-target' => '#importToXls',
                'class' => 'btn btn-default',
                'href' => Url::to(['/vendor/import-to-xls']),
                'style' => '',
            ],
        ])
    ?>
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu m-t-sm">
        <li>
            <a href="upload/template.xlsx" class="ng-binding">
                <i class="fa fa-list-alt m-r-xs"></i> Скачать шаблон
            </a>
        </li>
    </ul>
</div>
<button style="margin-right: 10px; margin-left: 10px;" class="btn btn-default m-t-xs m-r pull-right"><i class="fa fa-question-circle"></i> Инструкция</button>
</div>
<div class="panel-body">

<div class="handsontable" id="CreateCatalog"></div> 
</div>
<?php
$customJs = <<< JS
var data = [['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', '']];
var container = document.getElementById('CreateCatalog');
var hot = new Handsontable(container, {
  data: data,
  colHeaders : ['Артикул', 'Продукт', 'Количество', 'Цена (руб)'],
  columns: [
    {data: 'article'},
	{data: 'product'},
	{data: 'kolvo'},
	{data: 'price',type: 'numeric',format: '0,0.00'}
    ],
  className : 'Handsontable_table',
  rowHeaders : true,
  stretchH : 'all',
  autoRowSize: true,
  manualColumnResize: true,
  autoWrapRow: true,
  minSpareRows: 1,
  });
$('.save').click(function(e){	
e.preventDefault();
    var i, items, item, dataItem, data = [];
    var cols = [ 'article', 'product', 'units', 'price'];
    $('#CreateCatalog tr').each(function() {
	  items = $(this).children('td');
	  if(items.length === 0) {
	    return;
	  }
	  dataItem = {};
	  for(i = 0; i < cols.length; i+=1) {
	    item = items.eq(i);
	    if(item) {
	      dataItem[cols[i]] = item.html();
	    }
	  }
	  if(dataItem[cols[0]] || dataItem[cols[1]] || dataItem[cols[2]] || dataItem[cols[3]]){
	    data.push({dataItem});    
	  }	    
	});
	var catalog = data;
	catalog = JSON.stringify(catalog);
	$.ajax({
		  url: 'index.php?r=vendor/supplier-start-catalog-create',
		  type: 'POST',
		  dataType: "json",
		  data: $.param({'catalog':catalog}),
		  cache: false,
		  success: function (response) {
			  if(response.success){ 
			  bootbox.dialog({
			  message: response.message,
			  title: "Уведомление",
			  buttons: {
			    success: {
			      label: "Завершить",
			      className: "btn-success",
			      callback: function() {
				  location.reload();    
			      }
			    },
			  }
			});
		  }else{
		 // bootboxDialogShow(response.message);
		  console.log(response.message); 	  
		  }
	  },
      error: function(response) {
      console.log(response.message);
      }
    });
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>