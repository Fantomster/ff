<?php
use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use common\models\Category;
kartik\select2\Select2Asset::register($this);
\frontend\assets\HandsOnTableAsset::register($this);
?>
<?=
Modal::widget([
    'id' => 'view-supplier',
    'size' => 'modal-md',
    'clientOptions' => false,   
])
?>
<?=
Modal::widget([
    'id' => 'view-catalog',
    'size' => 'modal-lg',
    'clientOptions' => false,
])
?>
<?php
$this->title = 'Добавить поставщика';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
.Handsontable_table{position: relative;width: 100%;height:400px;overflow: hidden;}
.hide{dosplay:none}
');	

?>
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
        <button type="button" class="btn btn-gray" data-dismiss="modal">Отмена</button>
        <button id="invite" type="button" class="btn btn-success">Отправить</button>
      </div>
    </div>
  </div>
</div>
<?php /*
yii\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-warning',
    ],
    'body' => 'Для того, чтобы начать работу с новым поставщиком, посмотрите видео инструкцию. '
    . '<a class="btn btn-default btn-sm" href="#">Смотреть!</a>',
]); */
?>
<section class="content-header">
    <h1>
        <i class="fa fa-users"></i> Мои поставщики
        <small>Находите и добавляйте в Вашу систему новых поставщиков</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb'
        ],
        'links' => [
            'Мои поставщики'
        ],
    ])
    ?>
</section>



<section class="content">
    <div class="row">
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border">
                  <h3 class="box-title">Список поставщиков</h3>
            </div>
            <div class="box-body">
        <?php 
        $gridColumnsCatalog = [
            [
            'attribute'=>'organization_name',
            'label'=>'Организация',
            'format' => 'raw',
            'contentOptions' => ['class'=>'text-bold','style' => 'vertical-align:middle;width:45%;font-size:14px'],
            'value'=>function ($data) {
            return Html::a(Html::encode($data["organization_name"]), ['client/view-supplier', 'id' => $data["supp_org_id"]], [
                'data' => [
                'target' => '#view-supplier',
                'toggle' => 'modal',
                'backdrop' => 'static',
                          ],
                ]);
            }
            ],
            [
            'attribute'=>'status',
            'label'=>'Статус сотрудничества',
            'contentOptions' => ['style' => 'vertical-align:middle;width:45%;'],
            'format' => 'raw',
            'value'=>function ($data) {
                if($data["invite"]==0){ 
                $res = '<span class="text-primary"><i class="fa fa-circle-thin"></i> Ожидается подтверждение</span>';
                }else{
                    if(\common\models\User::find()->where(['email'=>\common\models\Organization::find()->
                        where(['id'=>$data["supp_org_id"]])->one()->email])->exists())
                        {    
                            $res = '<span class="text-yellow"><i class="fa fa-circle-thin"></i> Подтвержден / Не авторизован</span>';
                        }else{
                            $res = '<span class="text-success"><i class="fa fa-circle-thin"></i> Подтвержден</span> ';
                        }
                    } 
                    return $res;
                },
            ],
            [
            'label'=>'',
            'contentOptions' => ['style' => 'vertical-align:middle;width:10%;min-width:139px;'],
            'format' => 'raw',
            'value'=>function ($data) {
            $data["invite"]==0 ? $result = '' :
            $result = Html::a('Заказ', ['order/create',
                'OrderCatalogSearch[searchString]'=>"",
                'OrderCatalogSearch[selectedCategory]'=>"",
                'OrderCatalogSearch[selectedVendor]'=>$data["supp_org_id"],
                ],[
                    'class'=>'btn btn-outline-success btn-sm',
                    'data-pjax'=>0, 
                    'style'=>'margin-right:10px;text-center'
                  ]);
            $data["invite"]==0 ? $result .= '' :
            $result .= $data["cat_id"]==0 ? '' :
                Html::a('Каталог', ['client/view-catalog', 'id' => $data["cat_id"]], [
                'class'=>'btn btn-default btn-sm',
                'style'=>'text-center',
                'data-pjax'=>0,
                'data' => [
                'target' => '#view-catalog',
                'toggle' => 'modal',
                'backdrop' => 'static',
                   ],
                ]);
            
            return $result;
            }
            ]
        ];
        ?>
                <div class="box-body table-responsive no-padding">
                <?php Pjax::begin(['enablePushState' => false,'timeout' => 10000, 'id' => 'sp-list'])?>
                <?=GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterPosition' => false,
                    'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
                    'columns' => $gridColumnsCatalog, 
                    'filterPosition' => false,
                    'summary' => '',
                    'options' => ['class' => 'table-responsive'],
                    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
               'resizableColumns'=>false,
                ]);
                ?>  
                <?php Pjax::end(); ?> 
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php Pjax::begin(['enablePushState' => false,'timeout' => 10000, 'id' => 'add-supplier-list'])?>
                <?php $form = ActiveForm::begin(['id'=>'SuppliersFormSend']); ?>
        <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Добавить поставщика</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
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
            </div> 
            <div class="box-footer">
                <div class="form-group">
                    <?=Html::a('Добавить товары', ['#'], [
                      'class' => 'btn btn-success btn-sm',
                      'disabled' => 'disabled',
                      'name' => 'addProduct',
                      'id' => 'addProduct',
                      'data' => [
                      'target' => '#modal_addProduct',
                      'toggle' => 'modal',
                      'backdrop' => 'static',
                         ],
                      ]);?>
                </div>
                <div class="form-group">
                    <?= Html::submitButton('Пригласить', ['class' => 'btn btn-success hide', 'readonly' => 'readonly', 'name' => 'inviteSupplier','id' => 'inviteSupplier']) ?>
                </div>	    
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <?php Pjax::end(); ?>
    </div>
    </div>
</section>

<?php
//$this->registerCssFile('modules/handsontable/dist/handsontable.full.css');
//$this->registerCssFile('modules/handsontable/dist/bootstrap.css');
//$this->registerCssFile('modules/handsontable/dist/chosen.css');
//$this->registerCssFile('modules/handsontable/dist/pikaday/pikaday.css');
//$this->registerjsFile('modules/handsontable/dist/pikaday/pikaday.js');
//$this->registerjsFile('modules/handsontable/dist/moment/moment.js');
//$this->registerjsFile('modules/handsontable/dist/numbro/numbro.js');
//$this->registerjsFile('modules/handsontable/dist/zeroclipboard/ZeroClipboard.js');
//$this->registerjsFile('modules/handsontable/dist/numbro/languages.js');
//$this->registerJsFile('modules/handsontable/dist/handsontable.js');
//$this->registerJsFile('modules/handsontable/dist/handsontable-chosen-editor.js');
//$this->registerJsFile(Yii::$app->request->BaseUrl . '/modules/handsontable/dist/chosen.jquery.js', ['depends' => [yii\web\JqueryAsset::className()]]);
//$this->registerJsFile('modules/alerts.js');

$chkmailUrl = Url::to(['client/chkmail']);
$inviteUrl = Url::to(['client/invite']);
$createUrl = Url::to(['client/create']);

$customJs = <<< JS
$(".modal").removeAttr("tabindex");
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
$('.select2-search__field').css('width','100%');
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
  Controller: true,
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
            url: "$chkmailUrl",
            type: "POST",
            dataType: "json",
            data: {'email' : input.val()},
            success: function(response) {
            console.log(response)
                if(response.success){
	                if(response.eventType==1){
		        var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization);
	                $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log('type = 1'); 	    
	                }
	                
	                if(response.eventType==2){
		        var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization); 
	                $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log('type = 2');    
	                }
	                
	                if(response.eventType==3){
		        var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization);  
		            $('#addProduct').removeClass('hide');
                            $('#inviteSupplier').addClass('hide');
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').removeAttr('disabled');
                            console.log('type = 3');     
	                }
	                
	                if(response.eventType==4){
		            $('#addProduct').removeClass('hide');
                            $('#inviteSupplier').addClass('hide'); 
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log('type = 4');  
	                }
	                if(response.eventType==5){
		            $('#relationcategory-category_id').removeAttr('disabled');
                            $('#addProduct').removeClass('hide');
                            $('#inviteSupplier').addClass('hide').attr('disabled','disabled');
		            $('#profile-full_name, #organization-name').removeAttr('readonly','readonly');
                            console.log('type = 5');    
	                }
	                if(response.eventType==6){
		        var fio = response.fio;
                        var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization); 
	                $('#addProduct').addClass('hide');
	                $('#inviteSupplier').removeClass('hide');
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').removeAttr('disabled');
		            console.log('type = 6');    
	                }
                }else{
		    console.log(response.message); 
                }
            },
            error: function(response) {
		    console.log(response.message); 
            }
        }); 
	}
});
$('#profile-full_name,#organization-name').on('keyup paste put', function(e){
        console.log('ok');
    if($('#profile-full_name').val().length<2 || $('#organization-name').val().length<2){
        $('#inviteSupplier').attr('disabled','disabled');
        $('#addProduct').attr('disabled','disabled'); 
        }else{
        $('#inviteSupplier').removeAttr('disabled');
        $('#addProduct').removeAttr('disabled');   
        }
});
        
$('#inviteSupplier').click(function(e){
e.preventDefault();	
    $.ajax({
      url: '$inviteUrl',
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
                              $('#loader-show').hideLoading();
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
		  url: '$createUrl',
		  type: 'POST',
		  dataType: "json",
		  data: $("#SuppliersFormSend" ).serialize() + '&' + $.param({'catalog':catalog}),
		  cache: false,
		  success: function (response) {
                        if(response.success){
                          $('#loader-show').hideLoading();
                          $.pjax.reload({container: "#add-supplier-list"});
                          $.pjax.reload({container: "#sp-list"});
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
		  bootboxDialogShow(response.message);
		  console.log(response.message); 	  
		  }
	  },
        error: function(response) {
            $('#loader-show').hideLoading();
            }
        });
});       
$("#view-supplier").on("click", ".save-form", function() {             
    var form = $("#supplier-form");
    $.ajax({
    url: form.attr("action"),
    type: "POST",
    data: form.serialize(),
    cache: false,
    success: function(response) {
        $.pjax.reload({container: "#sp-list"});
            form.replaceWith(response);
                  
        },
        failure: function(errMsg) {
        console.log(errMsg);
    }
    });
});  
$("body").on("hidden.bs.modal", "#view-supplier", function() {
    $(this).data("bs.modal", null);
})
$("body").on("hidden.bs.modal", "#view-catalog", function() {
    $(this).data("bs.modal", null);
})
JS;
$this->registerJs($customJs, View::POS_READY);
?>
