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
    <h3 class="font-light pull-left" style="margin-top: 5px;"><i class="fa fa-list-alt"></i> Создание главного каталога</h3>
    <?= Html::a(
        'Сохранить',
        ['#'],
        ['class' => 'btn btn-success pull-right save','style' => ['margin-left'=>'5px']]
    ) ?>
    <?=
        Modal::widget([
            'id' => 'importToXls',
            'clientOptions' => false,
            'size'=>'modal-md',
            'toggleButton' => [
                'label' => '<i class="glyphicon glyphicon-import"></i> Импорт',
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
    <?=
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
        ])
    ?>
</div>
<div class="panel-body">
<div class="handsontable" id="CreateCatalog"></div> 
</div>
<?php
$categorys = json_encode(common\models\Category::allCategory(), JSON_UNESCAPED_UNICODE);




$customJs = <<< JS
var array = $categorys;
var datas = { "programs": [array] };
var arr = [];
$.each(datas.programs[0], function(key,val) {
    arr.push({'id': key, 'label' : val});
});
var data = [
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
];
var container = document.getElementById('CreateCatalog');
var hot = new Handsontable(container, {
  data: data,
  colHeaders : ['Артикул', 'Продукт', 'Кратность', 'Цена (руб)', 'Категория'],
  colWidths: [40, 180, 45, 45, 80],
  renderAllRows: true,
  columns: [
    {data: 'article'},
    {data: 'product'},
    {
        data: 'kolvo', 
        type: 'numeric',
    },
    {
        data: 'price', 
        type: 'numeric',
        format: '0.00',
        language: 'ru-RU'
    },
    {
        
        renderer: customDropdownRenderer,
        editor: "chosen",
        chosenOptions: {
            multiple: false,
            data: arr
            }
    },
    ],
  className : 'Handsontable_table',
  rowHeaders : true,
  stretchH : 'all',
  startRows: 1,
  autoWrapRow: true,
  });
function customDropdownRenderer(instance, td, row, col, prop, value, cellProperties) {
    var selectedId;
    var optionsList = cellProperties.chosenOptions.data;

    var values = (value + "").split(",");
    var value = [];
    for (var index = 0; index < optionsList.length; index++) {
        if (values.indexOf(optionsList[index].id + "") > -1) {
            selectedId = optionsList[index].id;
            value.push(optionsList[index].label);
        }
    }
    value = value.join(", ");

    Handsontable.TextCell.renderer.apply(this, arguments);
}
$('.save').click(function(e){	
e.preventDefault();
    var i, items, item, dataItem, data = [];
    var cols = [ 'article', 'product', 'units', 'price', 'category'];
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
	  if(dataItem[cols[0]] || dataItem[cols[1]] || dataItem[cols[2]] || dataItem[cols[3]] || dataItem[cols[4]]){
	    data.push({dataItem});    
	  }	    
	});
	var catalog = data;
        //console.log(data);
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