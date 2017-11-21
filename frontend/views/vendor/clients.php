<?php

use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use yii\web\View;
use kartik\checkbox\CheckboxX;

$this->title = 'Мои клиенты';
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
    'id' => 'view-client',
    'size' => 'modal-md',
    'clientOptions' => false,
])
?>
<?php
$gridColumnsClients = [
    [
        'label' => 'Ресторан',
        'format' => 'raw',
        'attribute' => 'client_name',
        'value' => function ($data) {
            return Html::a("<b>" . $data->client->name . "</b>", ['vendor/view-client', 'id' => $data->rest_org_id], [
                        'data' => [
                            'target' => '#view-client',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
            ]);
        }
    ],
    [
        'label' => 'Последний заказ',
        'format' => 'raw',
        'attribute' => 'last_order_date',
        'value' => function($data) {
            $date = isset($data->lastOrder) ? Yii::$app->formatter->asDatetime($data->lastOrder->updated_at, "php:j M Y") : 'Никогда';
            return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
        },
    ],
    /* [
      'label' => 'Текущий каталог',
      'format' => 'raw',
      'attribute' => 'catalog_name',
      'value' => function ($data) {
      return empty($data->cat_id) ? '' :
      Html::a($data->catalog->name, ['vendor/view-catalog', 'id' => $data->cat_id], [
      'data' => [
      'target' => '#view-catalog',
      'toggle' => 'modal',
      'backdrop' => 'static',
      ],
      'class' => 'current-catalog',
      ]);
      }
      ], */
    [
        'label' => 'Назначенные менеджеры',
        'format' => 'raw',
        'value' => function ($data) {
            $result = '';
            $managers = $data->client->getAssociatedManagersList($data->vendor->id);
            foreach ($managers as $manager) {
                $result .= "<div>$manager</div>";
            }
            return $result;
        },
    ],
    [
        'label' => 'Каталог',
        'attribute' => 'invite',
        'format' => 'raw',
        'contentOptions' => ['style' => 'text-align:left'],
        'value' => function ($data) {
//            $value = $data->invite == 0 ? 0 : 1;
//            $link = CheckboxX::widget([
//                        'name' => 'restOrgId_' . $data->rest_org_id,
//                        'initInputType' => CheckboxX::INPUT_CHECKBOX,
//                        'value' => $value,
//                        'autoLabel' => true,
//                        'options' => ['id' => 'restOrgId_' . $data->rest_org_id, 'data-id' => $data->rest_org_id, 'value' => $value],
//                        'pluginOptions' => [
//                            'threeState' => false,
//                            'theme' => 'krajee-flatblue',
//                            'enclosedLabel' => true,
//                            'size' => 'lg',
//                        ]
//            ]);
            if (!empty($data->invite) && !empty($data->cat_id)) {
                $result = Html::a($data->catalog->name, ['vendor/view-catalog', 'id' => $data->cat_id], [
                            'data' => [
                                'target' => '#view-catalog',
                                'toggle' => 'modal',
                                'backdrop' => 'static',
                            ],
                ]);
            } elseif (empty($data->invite)) {
                $result = "";
            } elseif (!empty($data->invite) && empty($data->cat_id)) {
                $result = Html::a("Каталог не назначен", ['vendor/view-client', 'id' => $data->rest_org_id], [
                            'data' => [
                                'target' => '#view-client',
                                'toggle' => 'modal',
                                'backdrop' => 'static',
                            ],
                            'style' => 'color: #cccccc;'
                ]);
            }
            return $result;
        },
    ],
    [
        'label' => '',
        'format' => 'raw',
        'contentOptions' => ['style' => 'width:30px;text-align:center'],
        'value' => function ($data) {
            $result = Html::button('<i class="fa fa-trash m-r-xs"></i>', [
                        'class' => 'btn btn-danger btn-sm del',
                        'data' => ['id' => $data->rest_org_id],
            ]);
            return $result;
        }
    ],
];
?>
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> Мои клиенты
        <small></small>
    </h1>
<?=
Breadcrumbs::widget([
    'options' => [
        'class' => 'breadcrumb',
    ],
    'links' => [
        'Мои клиенты'
    ],
])
?>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-header with-border">
<?=
Modal::widget([
    'id' => 'add-client',
    'clientOptions' => false,
    'toggleButton' => [
        'label' => 'Пригласить клиента',
        'tag' => 'a',
        'data-target' => '#add-client',
        'class' => 'btn btn-md btn-fk-success',
        'href' => Url::to(['/vendor/ajax-add-client']),
    ],
])
?>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="panel-body" style="padding-left: 0;">
<?php
$form = ActiveForm::begin([
            'options' => [
                'id' => 'search_form',
                'role' => 'search',
            ],
        ]);
?>
                <div class="col-sm-3">
                <?=
                        $form->field($searchModel, "search_string", [
                            'addon' => [
                                'append' => [
                                    'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                    'options' => [
                                        'class' => 'append',
                                    ],
                                ],
                            ],
                        ])
                        ->textInput(['prompt' => 'Поиск', 'class' => 'form-control', 'id' => 'search_string'])
                        ->label('Поиск', ['class' => 'label search_string', 'style' => 'color:#555'])
                ?>
                </div>
                <div class="col-sm-3">
<?=
        $form->field($searchModel, "cat_id")
        ->dropDownList($currentOrganization->getCatalogsList(), ['prompt' => 'Все', 'class' => 'form-control', 'id' => 'filter_catalog'])
        ->label("Каталог", ['class' => 'label filter_catalog', 'style' => 'color:#555'])
?>
                </div>
                <div class="col-sm-3">
<?=
        $form->field($searchModel, "invite")
        ->dropDownList([
            '0' => 'Не подтвержден',
            '1' => 'Подтвержден',
                ], ['prompt' => 'Все', 'class' => 'form-control', 'id' => 'filter_invite'])
        ->label("Статус", ['class' => 'label filter_invite', 'style' => 'color:#555'])
?>
                </div>
                <div class="col-sm-3 col-md-2 col-lg-1">
<?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
                    <?= Html::button('<i class="fa fa-times" aria-hidden="true"></i>', ['class' => 'form-control clear_filters btn btn-outline-danger teaser']) ?>
                </div>
                    <?php ActiveForm::end(); ?>
            </div>
            <div class="panel-body">
<?php Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'cl-list', 'timeout' => 5000]); ?>
                <?=
                GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterPosition' => false,
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                    'columns' => $gridColumnsClients,
                    'options' => ['class' => 'table-responsive'],
                    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
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
</section>
<?php
$inviteRestOrgUrl = Url::to(['vendor/ajax-invite-rest-org-id']);
$removeClientUrl = Url::to(['vendor/remove-client']);

$customJs = <<< JS
    $(document).on("change keyup paste cut", "#search_string", function() {
        if (timer) {
            clearTimeout(timer);
        }
        timer = setTimeout(function() {
            $("#search_form").submit();
        }, 700);
    });
                        
    $(document).on("change", "#filter_invite, #filter_catalog", function() {
        $("#search_form").submit();
    });
                        
    $('.clear_filters').on("click", function () {
           $('#search_string').val(''); 
           $('#filter_catalog').val(''); 
           $('#filter_invite').val('');
           $("#search_form").submit();
    });
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
$(document).on('change', 'input[type=checkbox]', function(e) {
    var id = $(this).attr('data-id');
    var elem = $(this).attr('name').substr(0, 9);
    var state = $(this).prop("checked");
        console.log(elem)
if(elem=="restOrgId"){invite(elem,state,id);}
function invite(elem,state,id){
		$.ajax({
	        url: "$inviteRestOrgUrl",
	        type: "POST",
	        dataType: "json",
	        data: {'elem' : elem,'state' : state, 'id' : id},
	        cache: false,
	        success: function(response) {
		        console.log(response)
		        $.pjax.reload({container: "#cl-list"});
		    },
		    failure: function(errMsg) {
	            console.log(errMsg);
	        }
		});
	}   
}) 
$("body").on("hidden.bs.modal", "#add-client", function() {
    $(this).data("bs.modal", null);
})
$("#add-client").on("click", ".adds-client", function() {
    var form = $("#add-client-form");
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            form.replaceWith(result);
        });
        return false;
    });
$("body").on("hidden.bs.modal", "#view-client", function() {
    $(this).data("bs.modal", null);
    $.pjax.reload({container: "#cl-list"});
});
$("body").on("hidden.bs.modal", "#view-catalog", function() {
    console.log('close catalog');
    $(this).data("bs.modal", null);       
});
$("#view-client").on("click", ".save-form", function() { 
    var form = $("#client-form");
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            form.replaceWith(result);
            
        });
        return false;
    });
$(document).on("click",".del", function(e){
    var id = $(this).attr('data-id');
        bootbox.confirm({
            title: "Удалить клиента?",
            message: "Клиент будет удален из Вашего списка клиентов", 
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
	        url: "$removeClientUrl",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) { 
			         
		        }	
		    });
                $.pjax.reload({container: "#cl-list"});
		}else{
		console.log('cancel');	
		}
	}});      
})
JS;
$this->registerJs($customJs, View::POS_READY);
?>