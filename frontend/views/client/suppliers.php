<?php
use yii\helpers\Html;
use yii\helpers\url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use kartik\select2\Select2;
?>
<?php
$this->title = 'Мои поставщики';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
.Handsontable_table{position: relative;width: 100%;height:400px;overflow: hidden;}
.modal-title {color: #69b3e3;text-align: center;font-size: 16px;font-weight: 900;}
.font-light {font-weight: 300;}
.hide{dosplay:none}
.modal-footer {border-top:1px solid #ccc;background-color: #ecf0f5}
');	
?>
<ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tabAddSuppliers">Добавить поставщика</a></li>
    <li><a data-toggle="tab" href="#tabMySuppliers">Мои поставщики</a></li>
</ul>
<div class="tab-content">
    <div id="tabAddSuppliers" class="tab-pane fade in active">
		<div class="row">
		  <div class="col-lg-5">
		    <?php $form = ActiveForm::begin(['id'=>'SuppliersFormSend']); ?>
		    <?= $form->field($user, 'email')?>
			<?= $form->field($profile, 'full_name')?>
			<?= $form->field($organization, 'name')?>
		    <?= $form->field($relationCategory, 'category')->widget(Select2::classname(), [
				    'data' => $category::allCategory(),
				    'theme' => Select2::THEME_BOOTSTRAP,
				    'language' => 'ru',
				    'options' => ['multiple' => true,'placeholder' => 'Выбрать категорию...'],
				    'pluginOptions' => [
				        'allowClear' => true
				    ],
				]);
			?>
		    <div class="form-group">
			  <?= Html::button('Добавить продукты', ['class' => 'btn btn-primary','data-keyboard' => 'false', 'data-backdrop' => 'static', 'readonly' => 'readonly', 'name' => 'addSupplier','id' => 'addProduct']) ?>
			</div>
			<div class="form-group">
			  <?= Html::submitButton('Пригласить', ['class' => 'btn btn-primary hide', 'readonly' => 'readonly', 'name' => 'inviteSupplier','id' => 'inviteSupplier']) ?>
			</div>				
			<?php ActiveForm::end(); ?>		
		  </div>
		</div>
    </div>
    <div id="tabMySuppliers" class="tab-pane fade">
    </div>
</div>
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
        <button id="invite" type="button" class="btn btn-info">Отправить</button>
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
function bootboxDialogShow(msg){
bootbox.dialog({
  message: msg,
  title: "Уведомление",
  buttons: {
    success: {
      label: "ОК",
      className: "btn-success",
    },
  }
});
}
$('#profile-full_name').attr('readonly','readonly');
$('#organization-name').attr('readonly','readonly');
$('#relationcategory-category').attr('disabled','disabled');
$('#addProduct').attr('disabled','disabled');
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
/*
* 1 Поставщик уже есть в списке контактов (лочим все кнопки)
* 2 Вы уже отправили приглашение этому поставщику, ожидается отклик поставщика (лочим кнопки)
* 3 Связи не найдено - просто invite (#inviteSupplier)
* 4 Данный email не может быть использован (лочим все кнопки)
* 5 Нет совпадений по Email (Новый поставщик и новый каталог)(#addSupplier)
* 6 форма передана не ajax (лочим все кнопки)	
*/
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
	                if(response.eventType==1){
		            var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization);
	                $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
					$('#inviteSupplier').attr('disabled','disabled');
		            $('#addProduct').attr('disabled','disabled');
		            
		            $('#profile-full_name').attr('readonly','readonly');
		            $('#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log(response.message);	    
	                }
	                
	                if(response.eventType==2){
		            var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization); 
	                $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
	                $('#inviteSupplier').attr('disabled','disabled');
		            $('#addProduct').attr('disabled','disabled');
		            
		            $('#profile-full_name').attr('readonly','readonly');
		            $('#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log(response.message);   
	                }
	                
	                if(response.eventType==3){
		            var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization);  
		            $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
		            $('#inviteSupplier').attr('disabled','disabled');
		            $('#addProduct').removeAttr('disabled');
		            
		            $('#profile-full_name').attr('readonly','readonly');
		            $('#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category').removeAttr('disabled');
		            bootboxDialogShow(response.message);
		            console.log(response.message);    
	                }
	                
	                if(response.eventType==4){
		            $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
		            $('#inviteSupplier').attr('disabled','disabled');
		            $('#addProduct').attr('disabled','disabled'); 
		            
		            $('#profile-full_name').attr('readonly','readonly');
		            $('#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log(response.message);  
	                }
	                if(response.eventType==5){
		            $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
		            $('#inviteSupplier').attr('disabled','disabled');
		            $('#addProduct').removeAttr('disabled');
		            
		            $('#profile-full_name').removeAttr('readonly');
		            $('#organization-name').removeAttr('readonly');
		            $('#relationcategory-category').removeAttr('disabled');
		            //bootboxDialogShow(response.message);
		            console.log(response.message);    
	                }
	                if(response.eventType==6){
		            var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization); 
	                $('#addProduct').addClass('hide');
	                $('#inviteSupplier').removeClass('hide');  
		            $('#inviteSupplier').removeAttr('disabled');
		            $('#addProduct').attr('disabled','disabled');
		            
		            $('#profile-full_name').attr('readonly','readonly');
		            $('#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category').removeAttr('disabled');
		            bootboxDialogShow(response.message);
		            console.log(response.message);    
	                }
                }else{
	                //bootboxDialogShow(response.message);
		            console.log(response.message); 
                }
            },
            error: function(response) {
               // bootboxDialogShow(response.message);
		        console.log(response.message); 
            }
        }); 
	}
});
$('#inviteSupplier').click(function(e){
e.preventDefault();	
	$.ajax({
	  url: 'index.php?r=restaurant/invite',
	  type: 'POST',
	  dataType: "json",
	  data: $("#SuppliersFormSend" ).serialize(),
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
		}
		console.log(response);  
      }
	});
});
$('#invite').click(function(e){
e.preventDefault();	
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
	$.ajax({
		  url: 'index.php?r=restaurant/create',
		  type: 'POST',
		  dataType: "json",
		  //data: {'profile':profile,'catalog':catalog},
		  data: $("#SuppliersFormSend" ).serialize() + '&' + $.param({'catalog':catalog}),
		  cache: false,
		  success: function (response) {
			  if(response.success){
			  $('#modal_addProduct').modal('hide'); 
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
		  //$('#invite').removeAttr('readonly');
		  bootboxDialogShow(response.message);
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
