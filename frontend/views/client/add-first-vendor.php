<?php

use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;

\frontend\assets\HandsOnTableAsset::register($this);

$this->registerCss(
        '
            .intl-tel-input.allow-dropdown input, .intl-tel-input.allow-dropdown input[type=text] {
                padding-left: 62px;
            }
            .intl-tel-input.allow-dropdown .flag-container {
                padding-bottom: 7px;
                padding-left: 15px;
            }
            .intl-tel-input.allow-dropdown {
                margin-left: 5%;
                display: inline-block;
                width: 42%;
            }
            .input_type_2_2 {
                width: 100%;
            }
            .form-control {
                display: inline-block;
            }
            .Handsontable_table{position: relative;width: 100%;height:400px;overflow: hidden;}
            .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control {
                background-color: #fff;
                opacity: 1;
                cursor: not-allowed;
            }
            .submit[disabled] {
                cursor: not-allowed;
            }
        '
);

$chkmailUrl = Url::to(['client/chkmail']);
$inviteUrl = Url::to(['client/invite']);
$createUrl = Url::to(['client/create']);
$suppliersUrl = Url::to(['client/suppliers']);
$removeSupplierUrl = Url::to(['client/remove-supplier']);

$customJs = <<< JS
    $(".modal").removeAttr("tabindex");
    $('#modal_addProduct').on('shown.bs.modal', function() {
    var data = [];
    for ( var i = 0; i < 60; i++ ) {
        data.push({article: '', product: '', units: '', price: '',  ed: '', notes: '',});
    }
      var container = document.getElementById('CreateCatalog');
      var hot = new Handsontable(container, {
      data: data,
      colHeaders : ['Наименование товара', 'Ед. измерения', 'Цена (руб)'],
      columns: [
            {data: 'product', wordWrap:true},
            {data: 'ed', allowEmpty: false},
            {
                data: 'price', 
                type: 'numeric',
                format: '0.00',
                language: 'ru-RU'
            }
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
    $("body").on("hidden.bs.modal", "#edit-catalog", function() {
        $(this).data("bs.modal", null);
    });
    $(document).on("afterValidate", "#SuppliersFormSend", function (event, messages, errorAttributes) {
        for (var input in messages) {
            if (messages[input] != "") {
                $("#" + input).tooltip({title: messages[input], placement: "auto right", container: "body"});
                $("#" + input).tooltip();
                $("#" + input).tooltip("show");
                break;
            }
        }
    });
        
    $(document).on('submit', '#SuppliersFormSend', function(e) {
        var form = $('#SuppliersFormSend');
        $.post(
            form.attr("action"),
            form.serialize()
        )
        .done(function(result) {
            if (!result.errors) {
                form.replaceWith(result.form);
                if (result.vendorFound) {
                    $("#addProduct").hide();
                    $("#inviteSupplier").show();
                } else {
                    $("#profile-full_name").focus();
                    $("#addProduct").show();
                    $("#inviteSupplier").hide();
                }
            } else {
                if (result.vendor_added) {
                    $("#user-email").tooltip({title: "Поставщик уже добавлен!", placement: "auto right", container: "body"});
                    $("#user-email").tooltip();
                    $("#user-email").tooltip("show");
                    return;
                }
                for (var input in result.messages) {
                    if (result.messages[input] != "") {
                        $("#" + input).tooltip({title: result.messages[input], placement: "auto right", container: "body"});
                        $("#" + input).tooltip();
                        $("#" + input).tooltip("show");
                        return;
                    }
                }
            }
        });
        return false;
    });
    $(document).on('click', '#inviteSupplier', function(e){
        e.preventDefault();
        $.ajax({
            url: '$inviteUrl',
            type: 'POST',
            dataType: "json",
            data: $("#SuppliersFormSend" ).serialize(),
            cache: false,
            success: function (response) {
                if(response.success){
                    swal({
                        title: "Поставщик добавлен!", 
                        text: response.message,
                        type: "success"}).then(
                        function(){ 
                            location.reload(); 
                        });
                    console.log(response);  
                }
            }
        });
    });
$(document).on('shown.bs.modal','#modal_addProduct', function() {
var data = [];
for ( var i = 0; i < 60; i++ ) {
    data.push({article: '', product: '', units: '', price: '',  ed: '', notes: '',});
}
  var container = document.getElementById('CreateCatalog');
  var hot = new Handsontable(container, {
  data: data,
  colHeaders : ['Наименование товара', 'Ед. измерения', 'Цена (руб)'],
  columns: [
        {data: 'product', wordWrap:true},
        {data: 'ed', allowEmpty: false},
	{
            data: 'price', 
            type: 'numeric',
            format: '0.00',
            language: 'ru-RU'
        }
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
$(document).on('click', '#addProduct', function (e){
  e.preventDefault();
  if ($(this).attr('disabled') == 'disabled') {
  e.stopPropagation();
  }
});
$(document).on('hidden.bs.modal','#modal_addProduct', function (e) {
  $('#CreateCatalog *').remove();
});
$('#invite').click(function(e){	
    $('#loader-show').showLoading();
    e.preventDefault();
	var i, items, item, dataItem, data = [];
	var cols = ['product', 'ed', 'price'];
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
	  if(dataItem[cols[0]] || dataItem[cols[1]] || dataItem[cols[2]]){
	    data.push({dataItem});    
	  }	    
	});
	var catalog = data;
	catalog = JSON.stringify(catalog);
        var form = $("#SuppliersFormSend")[0];
        var formData = new FormData(form);
        formData.append('catalog', catalog);
        $.ajax({
            processData: false,
            contentType: false,
            url: '$createUrl',
            type: 'POST',
            dataType: "json",
            data: formData, //$("#SuppliersFormSend" ).serialize() + '&' + $.param({'catalog':catalog}),
            cache: false,
            success: function (response) {
                if(response.success){
                    $('#loader-show').hideLoading();
                    swal({
                        title: "Поставщик добавлен!", 
                        text: response.message,
                        type: "success"}).then(
                        function(){ 
                            location.reload(); 
                        });
                }else{
                    $('#loader-show').hideLoading();
                    swal({
                        title: "Ошибка!", 
                        text: response.message,
                        type: "error"});
                }
            },
            error: function(response) {
                $('#loader-show').hideLoading();
            }
        });
});       
$(document).on("click",".del", function(e){
    var id = $(this).attr('data-id');
        bootbox.confirm({
            title: "Удалить поставщика?",
            message: "Поставщик будет удален из Вашего списка поставщиков", 
            buttons: {
                confirm: {
                    label: 'Удалить',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'Отмена',
                    className: 'btn-default'
                }
            },
            className: "danger-fk",
            callback: function(result) {
		if(result){
		$.ajax({
	        url: "$removeSupplierUrl",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) { 
			         
		        }	
		    });
        $.pjax.reload({container: "#sp-list"});
		}else{
		console.log('cancel');	
		}
	}});      
})
        
JS;
$this->registerJs($customJs, View::POS_READY);
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
    'size' => 'modal-big',
    'clientOptions' => false,
])
?>
<?php
$this->title = 'Поставщики';
$disabled = true;
?>
<div id="modal_addProduct" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="text-center">
                    <h4 class="modal-title">
                        Укажите товары, который Вы покупаете у поставщика
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
<div class="container1">
    <div class="row">
        <div class="col-lg-12">
            <p class = "head_p">Добавьте своих поставщиков в Ваш аккаунт</p>


            <p class = "p_head">Добавьте информацию о Ваших поставщиках и их продуктов. Нажмите "Продолжить" для завершения настроек</p>
            <button class = "button_head">Продолжить</button>
        </div>
        <div class="col-lg-6 col-md-12">
            <div class="block_wrap_info">
                <div class="block_wrap_info_p">
                    <p>Информация о поставщике</p>
                </div>
                <?= $this->render('suppliers/_vendorForm', compact('user', 'profile', 'organization', 'disabled')) ?>
            </div>
        </div>
        <div class="col-lg-6 col-md-12">
            <div class="block_wrap_info">
                <div class="block_wrap_info_p">
                    <p>Пригласить поставщика</p>
                </div>
                <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'sp-list']) ?>
                <?=
                yii\widgets\ListView::widget([
                    'dataProvider' => $dataProvider,
                    'itemView' => 'suppliers/_vendorList',
                    'summary' => '',
                ])
                ?>
                <?php Pjax::end(); ?> 
            </div>
        </div>

    </div>
</div>