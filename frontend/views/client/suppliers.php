<?php

use yii\widgets\Breadcrumbs;
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
<?=
Modal::widget([
    'id' => 'edit-catalog',
    'size' => 'modal-lg',
    'clientOptions' => false,
])
?>
<?php
$this->title = 'Поставщики';
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
<?php
if ($step == common\models\Organization::STEP_ADD_VENDOR) {
    $this->registerJs('
        $("document").ready(function(){
            $("#showVideo").modal("show");
            
            $("body").on("hidden.bs.modal", "#showVideo", function() {
                $("#showVideo").remove()
            });
        });
            ');

    echo yii\bootstrap\Alert::widget([
        'options' => [
            'class' => 'alert-warning fade in',
        ],
        'body' => 'Для того, чтобы продолжить работу с нашей системой, добавьте ваших поставщиков.'
        . '<a class="btn btn-default btn-sm" href="#">Сделаем это!</a>',
    ]);

    Modal::begin([
        'id' => 'showVideo',
        'header' => '<h4>Обучающее видео</h4>',
        'footer' => '<a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> Закрыть</a>',
    ]);
    ?>
    <div class="modal-body form-inline"> 
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/Cj85FCJOZbQ" frameborder="0" allowfullscreen=""></iframe>
        </div>
        <div style="padding-top: 15px;">
            Как начать работать в системе f-keeper, или Загрузка каталогов поставщиков
        </div>
    </div>
    <?php
    Modal::end();
}
?>
<section class="content-header">
    <h1>
        <i class="fa fa-users"></i> Поставщики
        <small>Находите и добавляйте в Вашу систему новых поставщиков</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb'
        ],
        'links' => [
            'Поставщики'
        ],
    ])
    ?>
</section>


<?php
$gridColumnsCatalog = [
    [
        'attribute' => 'organization_name',
        'label' => 'Организация',
        'format' => 'raw',
        'contentOptions' => ['class' => 'text-bold', 'style' => 'vertical-align:middle;width:45%;font-size:14px'],
        'value' => function ($data) {
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
        'attribute' => 'status_invite',
        'label' => 'Статус сотрудничества',
        'contentOptions' => ['style' => 'vertical-align:middle;min-width:180px;'],
        'format' => 'raw',
        'value' => function ($data) {
    if ($data["status_invite"] == 1) {
        return '<span class="text-danger">Ожидается<br>подтверждение</span>';
    }
    if ($data["status_invite"] == 2) {
        return '<span class="text-yellow">Подтвержден /<br> Не авторизован</span>';
    }
    if ($data["status_invite"] == 3) {
        return '<span class="text-success">Подтвержден</span>';
    }
}
    ],
    [
        'label' => '',
        'contentOptions' => ['class' => 'text-right', 'style' => 'vertical-align:middle;min-width:174px'],
        'headerOptions' => ['style' => 'text-align:right'],
        'format' => 'raw',
        'value' => function ($data) {
    $result = "";
    $data["invite"] == 0 ? $result .=Html::tag('span', '<i class="fa fa-shopping-cart m-r-xs"></i> Заказ', [
                        'class' => 'btn btn-success btn-sm',
                        'disabled' => 'disabled']) :
                    $result .= $data["cat_id"] == 0 ? $result .=Html::tag('span', '<i class="fa fa-shopping-cart m-r-xs"></i> Заказ', [
                        'class' => 'btn btn-success btn-sm',
                        'disabled' => 'disabled']) :
                    Html::a('<i class="fa fa-shopping-cart m-r-xs"></i> Заказ', ['order/create',
                        'OrderCatalogSearch[searchString]' => "",
                        'OrderCatalogSearch[selectedCategory]' => "",
                        'OrderCatalogSearch[selectedVendor]' => $data["supp_org_id"],
                            ], [
                        'class' => 'btn btn-success btn-sm',
                        'data-pjax' => 0,
    ]);
    $data["invite"] == 0 ? $result .=Html::tag('span', '<i class="fa fa-eye m-r-xs"></i>', [
                        'class' => 'btn btn-default btn-sm',
                        'disabled' => 'disabled']) :
                    $result .= $data["cat_id"] == 0 ? $result .=Html::tag('span', '<i class="fa fa-eye m-r-xs"></i>', [
                        'class' => 'btn btn-default btn-sm',
                        'disabled' => 'disabled']) :
                    $data["status_invite"] == 2 ?
                            Html::a('<i class="fa fa-pencil"></i>', ['client/edit-catalog', 'id' => $data["cat_id"]], [
                                'class' => 'btn btn-default btn-sm',
                                'style' => 'text-center',
                                'data-pjax' => 0,
                                'data' => [
                                    'target' => '#edit-catalog',
                                    'toggle' => 'modal',
                                    'backdrop' => 'static',]
                            ]) :
                            Html::a('<i class="fa fa-eye"></i>', ['client/view-catalog', 'id' => $data["cat_id"]], [
                                'class' => 'btn btn-default btn-sm',
                                'style' => 'text-center',
                                'data-pjax' => 0,
                                'data' => [
                                    'target' => '#view-catalog',
                                    'toggle' => 'modal',
                                    'backdrop' => 'static',
                                ],
    ]);
    $data["status_invite"] == 2 ?
                    $result .= Html::a('<i class="fa fa-envelope m-r-xs"></i>', ['client/re-send-email-invite',
                        'id' => $data["supp_org_id"],
                            ], [
                        'class' => 'btn btn-default btn-sm',
                        'data-pjax' => 0,]) : $result .=Html::tag('span', '<i class="fa fa-envelope m-r-xs"></i>', [
                        'class' => 'btn btn-default btn-sm',
                        'disabled' => 'disabled']);

    $result .= Html::a('<i class="fa fa-trash m-r-xs"></i>', ['client/remove-supplier',
                'id' => $data["supp_org_id"],
                    ], [
                'class' => 'btn btn-danger btn-sm remove-supplier',
                'data-pjax' => 0,]);
    return "<div class='btn-group'>" . $result . "</div>";
}
    ],
];
?>
<section class="content">
    <div class="row">
        <div class="col-sm-12 col-md-8">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Список поставщиков</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-search"></i>
                                </span>
                                <?= Html::input('text', 'search', $searchString, ['class' => 'form-control', 'placeholder' => 'Поиск', 'id' => 'search']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box-body table-responsive no-padding">
                                <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'sp-list']) ?>
                                <?=
                                GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'filterPosition' => false,
                                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                    'columns' => $gridColumnsCatalog,
                                    'filterPosition' => false,
                                    'summary' => '',
                                    'options' => ['class' => 'table-responsive'],
                                    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
                                    'resizableColumns' => false,
                                ]);
                                ?>  
                                <?php Pjax::end(); ?> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'add-supplier-list']) ?>
            <?php $form = ActiveForm::begin(['id' => 'SuppliersFormSend']); ?>
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Добавить поставщика</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <?= $form->field($user, 'email') ?>
                    <?= $form->field($profile, 'full_name')->label('ФИО') ?>
                    <?= $form->field($organization, 'name')->label('Организация') ?>
                    <?=
                    $form->field($relationCategory, 'category_id')->label('Категория поставщика')->widget(Select2::classname(), [
                        'data' => Category::allCategory(),
                        'theme' => 'krajee',
                        //'language' => 'ru',
                        'hideSearch' => true,
                        'options' => ['multiple' => true, 'placeholder' => 'Выберите категорию'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]);
                    ?>
                </div> 
                <div class="box-footer">
                    <div class="form-group">
                        <?=
                        Html::a('Добавить товары', ['#'], [
                            'class' => 'btn btn-success btn-sm',
                            'disabled' => 'disabled',
                            'name' => 'addProduct',
                            'id' => 'addProduct',
                            'data' => [
                                'target' => '#modal_addProduct',
                                'toggle' => 'modal',
                                'backdrop' => 'static',
                            ],
                        ]);
                        ?>
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton('Пригласить', ['class' => 'btn btn-success hide', 'readonly' => 'readonly', 'name' => 'inviteSupplier', 'id' => 'inviteSupplier']) ?>
                    </div>	    
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>

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
var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'GET',
        push: false,
        timeout: 10000,
        url: 'index.php?r=client/suppliers',
        container: '#sp-list',
        data: {searchString: $('#search').val()}
      })
   }, 700);
});
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

$('#modal_addProduct').on('shown.bs.modal', function() {
var data = [];
for ( var i = 0; i < 60; i++ ) {
    data.push({article: '', product: '', units: '', price: '',  ed: '', notes: '',});
}
  var container = document.getElementById('CreateCatalog');
  var hot = new Handsontable(container, {
  data: data,
  colHeaders : ['Артикул', 'Наименование товара', 'Кратность', 'Цена (руб)', 'Ед. измерения', 'Комментарий'],
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
        {data: 'ed', allowEmpty: false},
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
* 3 Поставщик еще не авторизован / добавляем каталог
* 4 Данный email не может быть использован (лочим все кнопки)
* 5 Нет совпадений по Email (Новый поставщик и новый каталог)(#addSupplier)
* 6 поставщик авторизован, связи не найдено, invite	
*/
$('#profile-full_name').attr('readonly','readonly');
$('#organization-name').attr('readonly','readonly');
$('#relationcategory-category_id').attr('disabled','disabled');
$('.select2-search__field').css('width','100%');
$('#addProduct').attr('disabled','disabled');
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
            console.log(response)
                if(response.success){
                        // Поставщик уже есть в списке контактов (лочим все кнопки)
	                if(response.eventType==1){ 
		        var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization); 
	                $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
                        $('#addProduct').attr('disabled','disabled');
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log(organization);    
	                }
	                // Вы уже отправили приглашение этому поставщику, ожидается отклик поставщика (лочим кнопки)
	                if(response.eventType==2){
		        var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization); 
	                $('#addProduct').removeClass('hide');
	                $('#inviteSupplier').addClass('hide');
                        $('#addProduct').attr('disabled','disabled');
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log('type = 2');    
	                }
	                // Связи не найдено - просто invite (#inviteSupplier)
	                if(response.eventType==3){
		        var fio = response.fio;
	                var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization);  
		            $('#addProduct').removeClass('hide');
                            $('#inviteSupplier').addClass('hide');
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id,#addProduct').removeAttr('disabled');
                            console.log('type = 3');     
	                }
	                // Данный email не может быть использован (лочим все кнопки)
	                if(response.eventType==4){
		            $('#addProduct').removeClass('hide');
                            $('#inviteSupplier').addClass('hide');
                            $('#addProduct').attr('disabled','disabled'); 
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
		            $('#relationcategory-category_id').attr('disabled','disabled');
		            bootboxDialogShow(response.message);
		            console.log('type = 4');  
	                }
                        // Нет совпадений по Email (Новый поставщик и новый каталог)(#addSupplier)
	                if(response.eventType==5){
		            $('#relationcategory-category_id,#addProduct').removeAttr('disabled');
                            $('#addProduct').removeClass('hide');
                            $('#inviteSupplier').addClass('hide').attr('disabled','disabled');
		            $('#profile-full_name,#organization-name').removeAttr('readonly');
                            console.log('type = 5');    
	                }
                        // 
	                if(response.eventType==6){
		        var fio = response.fio;
                        var organization = response.organization;
	                $('#profile-full_name').val(fio);
	                $('#organization-name').val(organization); 
	                $('#addProduct').addClass('hide');
	                $('#inviteSupplier').removeClass('hide');
		            $('#profile-full_name,#organization-name').attr('readonly','readonly');
                            $('#relationcategory-category_id,#inviteSupplier').removeAttr('disabled');
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
	var cols = [ 'article', 'product', 'units', 'price', 'ed', 'note'];
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
	  if(dataItem[cols[0]] || dataItem[cols[1]] || dataItem[cols[2]] || dataItem[cols[3]] || dataItem[cols[4]] || dataItem[cols[5]]){
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
$(document).on("click", ".resend-invite", function(e) {     
    e.preventDefault();
    var url = $(this).attr('href');
    console.log(url);
    bootbox.confirm({
        title:"Приглашение на F-keeper",
        message: "Отправить приглашение повторно?",
        buttons: {
            cancel: {
                label: 'Закрыть',
                className: 'btn-gray'
            },
            confirm: {
                label: 'Отправить',
                className: 'btn-success'
            }
            
        },
        callback: function (result) {
         if(result)$.post(url, {} );
        }
    });
});
$(document).on("click", ".remove-supplier", function(e) {     
    e.preventDefault();
    var url = $(this).attr('href');
    console.log(url);
    bootbox.confirm({
        title:"Удаление поставщика",
        message: "Удалить поставщика из вашего списка?",
        buttons: {
            cancel: {
                label: 'Отмена',
                className: 'btn-gray'
            },
            confirm: {
                label: 'Удалить',
                className: 'btn-success'
            }
            
        },
        callback: function (result) {
         if(result){ 
             $.post(url, function( data ) { $.pjax.reload({container: "#sp-list"}); });
                
            }
        }
    });
});        
$("body").on("hidden.bs.modal", "#view-supplier", function() {
    $(this).data("bs.modal", null);
})
$("body").on("hidden.bs.modal", "#view-catalog", function() {
    $(this).data("bs.modal", null);
})
$("body").on("hidden.bs.modal", "#resend", function() {
    $(this).data("bs.modal", null);
})
$("body").on("hidden.bs.modal", "#edit-catalog", function() {
    $(this).data("bs.modal", null);
})
JS;
$this->registerJs($customJs, View::POS_READY);
?>
