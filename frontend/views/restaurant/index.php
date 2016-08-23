<?php

use yii\helpers\Html;
use yii\helpers\url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
?>
<?php
$this->title = 'my-suppliers';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
.nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {border-top: 4px solid #69b3e3;	}
.nav-tabs > li > a, .nav-tabs > li> a:hover, .nav-tabs > li> a:focus {border-top: 4px solid rgba(255, 255, 255, 0);	}
.tab-content{border-left: 1px solid #ddd;border-right: 1px solid #ddd;border-bottom: 1px solid #ddd;}
.Handsontable_table{position: relative;width: 100%;height:400px;overflow: hidden;}
.modal-title {color: #69b3e3;text-align: center;font-size: 16px;font-weight: 900;}
.font-light {font-weight: 300;}
.hide{dosplay:none}
.modal-footer {border-top:1px solid #ccc;background-color: #ecf0f5}
');
?>

<h1>Форма для ресторана</h1>
<div class="row">
    <div class="col-lg-5">
        <?php $form = ActiveForm::begin(['id' => 'SuppliersFormSend']); ?>

        <?= $form->field($user, 'email') ?>

        <?= $form->field($profile, 'full_name') ?>

        <?= $form->field($organization, 'name') ?>
        
        <?= $form->field($relation_category, 'category')->dropDownList($relation_category::allCategory(), ['prompt' => '']); ?>
        
        <div class="form-group">
            <?= Html::button('Добавить продукты', ['class' => 'btn btn-primary', 'data-keyboard' => 'false', 'data-backdrop' => 'static', 'disabled' => 'disabled', 'name' => 'addSupplier', 'id' => 'addProduct']) ?>
        </div>
        <div class="form-group">
            <?= Html::submitButton('Пригласить', ['class' => 'btn btn-primary hide', 'disabled' => 'disabled', 'name' => 'inviteSupplier', 'id' => 'inviteSupplier']) ?>
        </div>				
<?php ActiveForm::end(); ?>		
    </div>
</div>
<!-- Modal -->
<?php
/*
  Modal::begin([
  'header'=>'
  <div class="text-center">
  <h3 class="modal-title">
  Добавить / Изменить Продукты
  </h3>
  <span class="font-light">Введите всю информацию о вашей продукции ниже для этого поставщика. Эта таблица ведет себя так же, как Excel!<br><strong>Пожалуйста, обратите внимание , что вся информация необходима для заказа.</strong>
  </span>
  </div>',
  'id'=>'modal_addProduct',
  'size'=>'modal-lg',
  'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
  ]);
  echo "
  <div class=\"modal-body\">
  <div id='CreateCatalog'></div>
  </div>
  <div class=\"modal-footer\">
  <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Отмена</button>
  <button id=\"invite\" type=\"button\" class=\"btn btn-info\">Пригласить</button>
  </div>";
  Modal::end();
 */
?>
<!-- Modal -->
<div id="modal_addProduct" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="text-center">
                    <h3></h3>
                    <h3 class="modal-title">
                        Добавить / Изменить Продукты
                    </h3>
                    <span class="font-light">Введите всю информацию о вашей продукции ниже для этого поставщика. Эта таблица ведет себя так же, как Excel!<br><strong>Пожалуйста, обратите внимание , что вся информация необходима для заказа.</strong></span>
                </div>
            </div>
            <div class="modal-body">
                <div class="handsontable" id="CreateCatalog"></div>   
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button id="invite" type="button" class="btn btn-info">Пригласить</button>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerCssFile('modules/handsontable/dist/handsontable.full.css');
$this->registerCssFile('modules/handsontable/dist/pikaday/pikaday.css');
$this->registerjsFile('modules/handsontable/dist/pikaday/pikaday.js');
$this->registerjsFile('modules/handsontable/dist/moment/moment.js');
$this->registerjsFile('modules/handsontable/dist/numbro/numbro.js');
$this->registerjsFile('modules/handsontable/dist/zeroclipboard/ZeroClipboard.js');
$this->registerjsFile('modules/handsontable/dist/numbro/languages.js');
$this->registerJsFile('modules/handsontable/dist/handsontable.js');
//$this->registerJsFile('modules/alerts.js');
$customJs = <<< JS
$('#modal_addProduct').on('shown.bs.modal', function() {
var data = [['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', ''],['', '', '', '']];
var container = document.getElementById('CreateCatalog');
var hot = new Handsontable(container, {
  data: data,
  colHeaders : ['Артикул', 'Продукт', 'Количество', 'Цена (руб)', 'Комментарий'],
  columns: [
    {data: 'article'},
	{data: 'product'},
	{data: 'kolvo'},
	{data: 'price',type: 'numeric',format: '0,0.00 $',language: 'ru-RU'},
    {data: 'note'}
    ],
  className : 'Handsontable_table',
  rowHeaders : true,
  stretchH : 'all',
  autoRowSize: true,
  manualColumnResize: true,
  autoWrapRow: true,
  minSpareRows: 1,
  })   
});
$('#addProduct').click(function (e){
  e.preventDefault();
  $('#modal_addProduct').modal('show');
});
$('#modal_addProduct').on('hidden.bs.modal', function (e) {
  $('#CreateCatalog *').remove();
});
$('#SuppliersFormSend').on('afterValidateAttribute', function (event, attribute, messages) {
	
	var hasError = messages.length !==0;
    var field = $(attribute.container);
    var input = field.find(attribute.input);
	input.attr("aria-invalid", hasError ? "true" : "false");
    if (attribute.name === 'email' && !hasError)
    {
		$.ajax({
            url: "index.php?r=restaurant/chkmail",
            type: "POST",
            dataType: "json",
            data: {'email' : input.val()},
            success: function(response) {
                if(response.success){
	                var fio = response.fio;
	                var organization = response.organization;
	                $('#user-username').val(fio);
	                $('#organization-name').val(organization);
	                $('#addProduct').attr('disabled','disabled');
	                $('#inviteSupplier').removeAttr('disabled');
	                $('#inviteSupplier').removeClass('hide');
	                $('#addProduct').addClass('hide');
	                console.log(response.message);
                }else{
	                $('#inviteSupplier').addClass('hide');
	                $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').attr('disabled','disabled');
	                $('#addProduct').removeAttr('disabled');
	                console.log(response.message);
                }
            },
            error: function(response) {
                console.log(response.message);
            }
        }); 
	}else{
     //$('#addProduct').attr('disabled','disabled');
	 return false;	 
	}
});
$('#invite').click(function(e){
e.preventDefault();	
	var email = $('#user-email').val();
	var	fio = $('#user-username').val();
	var	organization = $('#organization-name').val();
	var	category = $('#relationcategory-category').val(); 
	var i, items, item, dataItem, data = [];
	var cols = [ 'article', 'product', 'units', 'price', 'note'];
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
	catalog = JSON.stringify(catalog);
	var profile =[];
	profile.push({'profile' : {'email' : email,'username' : fio,'organization' : organization, 'category' : category}});
	profile = JSON.stringify(profile);
	$.ajax({
	  url: 'index.php?r=restaurant/create',
	  type: 'POST',
	  dataType: "json",
//	  data: {'profile':profile,'catalog':catalog},
          data: $("#SuppliersFormSend" ).serialize() + '&' + $.param({'catalog':catalog}),
	  cache: false,
	  success: function (response) {
		  console.log(response.message);
	  },
      error: function(response) {
        console.log(response.message);
      }
	});
});
/*
$('#CreateCatalog').on('afterValidate', function(result){
   if(!result.isValid){
	   alert('Take your pants off cause we have a problem boy! - value - '+ result.value +' row -' + result.row +' prop -' + result.prop+ +' source-'+result.source); 
   }
});
*/

JS;
$this->registerJs($customJs, View::POS_READY);
?>
