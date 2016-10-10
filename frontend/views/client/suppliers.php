<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use common\models\Category;
kartik\select2\Select2Asset::register($this);
?>
<?php
$this->title = 'Мои поставщики';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
.Handsontable_table{position: relative;width: 100%;height:400px;overflow: hidden;}
.hide{dosplay:none}
');	
?>
<?=
Modal::widget([
    'id' => 'view-catalog',
    'size' => 'modal-lg',
    'clientOptions' => false,   
])
?>
<?=
Modal::widget([
    'id' => 'view-supplier',
    'size' => 'modal-md',
    'clientOptions' => false,   
])
?>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Мои поставщики</h3>
        <span class="pull-right"></span>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tabMySuppliers">Мои поставщики</a></li>
                <li><a data-toggle="tab" href="#tabAddSuppliers">Добавить поставщика</a></li>    
            </ul>
        <div>
        <div class="tab-content">
            <div id="tabMySuppliers" class="tab-pane fade in active">

    <?php 
$gridColumnsCatalog = [
        [
        'label'=>'Организация',
        'format' => 'raw',
        'value'=>function ($data) {
        $res = common\models\Organization::find()->where(['id'=>$data->supp_org_id])->one()->name;
        return Html::a(Html::encode($res), ['client/view-supplier', 'id' => $data->supp_org_id], [
            'data' => [
            'target' => '#view-supplier',
            'toggle' => 'modal',
            'backdrop' => 'static',
                      ],
            ]);
        }
        ],
        [
        'label'=>'email',
        'contentOptions' => ['class' => ''],
        'value'=>function ($data) {
        return common\models\Organization::find()->where(['id'=>$data->supp_org_id])->one()->email;
        }
        ],
        [
        'label'=>'Статус сотрудничества',
        'contentOptions' => ['class' => ''],
        'format' => 'raw',
        'value'=>function ($data) {
                $status_invite = $data->invite==0 ? '<span class="text-danger">Ожидается<br>подтверждение</span>':'<span class="text-primary">Подтвержден</span>';
                return $status_invite;
                },
        ],
        [
        'label'=>'Каталог',
        'contentOptions' => ['class' => 'text-wrap'],
        'format' => 'raw',
        'value'=>function ($data) {
        $cat = common\models\Catalog::find()->where(['id'=>$data->cat_id])->one();
        $data->invite==0 ? $result = '' :
        $result = $data->cat_id==0 ? 'Каталог не назначен' :
            Html::a('<i class="fa fa-list-alt" aria-hidden="true"></i>', ['client/view-catalog', 'id' => $data->cat_id], [
            'data' => [
            'target' => '#view-catalog',
            'toggle' => 'modal',
            'backdrop' => 'static',
               ],
            ]);
        return $result;
        }
        ],
];
?>
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">
                    <?php Pjax::begin(['enablePushState' => false,'timeout' => 10000, 'id' => 'sp-list'])?>
                    <?=GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'filterPosition' => false,
                        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
                        'columns' => $gridColumnsCatalog, 
                        'tableOptions' => ['class' => 'table no-margin'],
                        'options' => ['class' => 'table-responsive'],
                        'bordered' => false,
                        'striped' => true,
                        'condensed' => false,
                        'responsive' => false,
                        'hover' => false,
                    ]);
                    ?>  
                    <?php Pjax::end(); ?> 
                    </div>
                </div>
            </div>
            <div id="tabAddSuppliers" class="tab-pane fade">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-5">
                            <?php $form = ActiveForm::begin(['id'=>'SuppliersFormSend']); ?>
                                <?= $form->field($user, 'email')?>
                                <?= $form->field($profile, 'full_name')->label('ФИО')?>
                                <?= $form->field($organization, 'name')->label('Организация')?>
                                <?= $form->field($relationCategory, 'category_id')->label('Категория поставщика')->widget(Select2::classname(), [
                                    'data' => Category::allCategory(),
                                    'theme' => 'krajee',
                                    //'language' => 'ru',
                                    'hideSearch' => true,
                                    'options' => ['multiple' => true,'placeholder' => 'Выберите категорию'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]);
                                ?>
                            <div class="form-group">
                            <?=Html::a('Добавить продукты', ['#'], [
                              'class' => 'btn btn-primary btn-sm',
                              'disabled' => 'disabled',
                              'name' => 'addSupplier',
                              'id' => 'addProduct',
                              'data' => [
                              'target' => '#modal_addProduct',
                              'toggle' => 'modal',
                              'backdrop' => 'static',
                                 ],
                              ]);?>
                            </div>
                            <div class="form-group">
                                <?= Html::submitButton('Пригласить', ['class' => 'btn btn-primary hide', 'readonly' => 'readonly', 'name' => 'inviteSupplier','id' => 'inviteSupplier']) ?>
                            </div>				
                            <?php ActiveForm::end(); ?>		
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
<div id="modal_addProduct" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <div class="text-center">
		<h4 class="modal-title">
			Добавить / Изменить Продукты
		</h4>
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
//$this->registerJsFile('modules/alerts.js');
$customJs = <<< JS
/** 
 * Forward port jQuery.live()
 * Wrapper for newer jQuery.on()
 * Uses optimized selector context 
 * Only add if live() not already existing.
*/
if (typeof jQuery.fn.live == 'undefined' || !(jQuery.isFunction(jQuery.fn.live))) {
  jQuery.fn.extend({
      live: function (event, callback) {
         if (this.selector) {
              jQuery(document).on(event, this.selector, callback);
          }
      }
  });
}
function bootboxDialogShow(msg){
bootbox.dialog({
    message: msg,
    title: 'Уведомление',
    buttons: {
        success: {
          label: "Окей!",
          className: "btn-success btn-md",
          callback: function() {
            //location.reload();    
          }
        },
    },
});
}
$('#profile-full_name').attr('readonly','readonly');
$('#organization-name').attr('readonly','readonly');
$('#relationcategory-category_id').attr('disabled','disabled');
$('.select2-search__field').css('width','100%')
$('#addProduct').attr('disabled','disabled');
$('#modal_addProduct').on('shown.bs.modal', function() {
var data = [];
for ( var i = 0; i < 60; i++ ) {
    data.push({article: '', product: '', units: '', price: '', notes: ''});
}
  var container = document.getElementById('CreateCatalog');
  var hot = new Handsontable(container, {
  data: data,
  colHeaders : ['Артикул', 'Наименование товара', 'Кратность', 'Цена (руб)', 'Комментарий'],
  columns: [
        {data: 'article'},
        {data: 'product', wordWrap:true},
	{
            data: 'units', 
            type: 'numeric'
        },
	{
            data: 'price', 
            type: 'numeric',
            format: '0.00',
            language: 'ru-RU'
        },
        {data: 'note'}
    ],
  className : 'Handsontable_table',
  rowHeaders : true,
  renderAllRows: true,
  stretchH : 'all',
  autoRowSize: true,
  manualColumnResize: true,
  autoWrapRow: true,
  minSpareRows: 1,
  tableClassName: ['table-hover']
  })   
});
$('#addProduct').click(function (e){
  e.preventDefault();
  if ($(this).attr('disabled') == 'disabled') {
  e.stopPropagation();
  }
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
            url: "index.php?r=client/chkmail",
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
		            $('#relationcategory-category_id').attr('disabled','disabled');
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
		            $('#relationcategory-category_id').attr('disabled','disabled');
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
		            $('#relationcategory-category_id').removeAttr('disabled');
		            //bootboxDialogShow(response.message);
		            console.log(response.message);    
	                }
	                
	                if(response.eventType==4){
		            $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
		            $('#inviteSupplier').attr('disabled','disabled');
		            $('#addProduct').attr('disabled','disabled'); 
		            
		            $('#profile-full_name').attr('readonly','readonly');
		            $('#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').attr('disabled','disabled');
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
		            $('#relationcategory-category_id').removeAttr('disabled');
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
		            $('#relationcategory-category_id').removeAttr('disabled');
		            //bootboxDialogShow(response.message);
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
      url: 'index.php?r=client/invite',
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
 $('#loader-show').showLoading();
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
		  url: 'index.php?r=client/create',
		  type: 'POST',
		  dataType: "json",
		  data: $("#SuppliersFormSend" ).serialize() + '&' + $.param({'catalog':catalog}),
		  cache: false,
		  success: function (response) {
                        if(response.success){
                          $('#loader-show').hideLoading();
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
                  $('#loader-show').hideLoading();
		  //$('#invite').removeAttr('readonly');
		  bootboxDialogShow(response.message);
		  console.log(response.message); 	  
		  }
	  },
      error: function(response) {
      $('#loader-show').hideLoading();
      console.log(response.message);
      }
	});
});
$("body").on("hidden.bs.modal", "#view-supplier", function() {
    $(this).data("bs.modal", null);
    //$.pjax.reload({container: "#sp-list"});
})
$("body").on("hidden.bs.modal", "#view-catalog", function() {
    $(this).data("bs.modal", null);
})
$("#view-supplier").on("click", ".save-form", function() {        
    var form = $("#supplier-form");
    console.log(form.serialize())
    $.ajax({
    url: form.attr("action"),
    type: "POST",
    //dataType: "json",
    data: form.serialize(),
    cache: false,
    success: function(response) {
            form.replaceWith(response);
        },
        failure: function(errMsg) {
        console.log(errMsg);
    }
    });
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>
