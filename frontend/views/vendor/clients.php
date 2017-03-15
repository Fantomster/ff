<?php

use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use dosamigos\switchinput\SwitchBox;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use common\models\Users;
use kartik\checkbox\CheckboxX;
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
        'value' => function ($data) {
            $res = common\models\Organization::find()->where(['id' => $data['rest_org_id']])->one()->name;
            return Html::a("<b>" . Html::encode($res) . "</b>", ['vendor/view-client', 'id' => $data['rest_org_id']], [
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
                'value' =>
                function($data) {
                    $res = common\models\Order::find()->select('MAX(CAST(created_at AS CHAR)) as created_at')->where(['client_id' => $data['rest_org_id'], 'vendor_id' => common\models\User::findIdentity(Yii::$app->user->id)->organization_id])->one()->created_at;
                    $date = Yii::$app->formatter->asDatetime($res, "php:j M Y");
                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                },
                    ],
                    [
                        'label' => 'Текущий каталог',
                        'format' => 'raw',
                        'value' => function ($data) {
                            $cat = common\models\Catalog::find()->where(['id' => $data['cat_id']])->one();
                            return $data['cat_id'] == 0 ? '' :
                                    Html::a(Html::encode($cat->name), ['vendor/view-catalog', 'id' => $data['cat_id']], [
                                        'data' => [
                                            'target' => '#view-catalog',
                                            'toggle' => 'modal',
                                            'backdrop' => 'static',
                                        ],
                                        'class' => 'current-catalog',
                            ]);
                        }
                            ],
                            [
                                'attribute' => 'Статус сотрудничества',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'width:190px;text-align:center'],
                                'value' => function ($data) {
                            $link = CheckboxX::widget([
                                        'name' => 'restOrgId_' . $data['rest_org_id'],
                                        'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                        'value' => $data['invite'] == 0 ? 0 : 1,
                                        'autoLabel' => true,
                                        'options' => ['id' => 'restOrgId_' . $data['rest_org_id'], 'data-id' => $data['rest_org_id']],
                                        'pluginOptions' => [
                                            'threeState' => false,
                                            'theme' => 'krajee-flatblue',
                                            'enclosedLabel' => true,
                                            'size' => 'lg',
                                        ]
                            ]);
                            return $link;
                        },
                            ],
                            [
                        'label' => '',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'width:30px;text-align:center'],
                        'value' => function ($data) {
                            $result = Html::button('<i class="fa fa-trash m-r-xs"></i>', [
                                    'class' => 'btn btn-danger btn-sm del',
                                    'data' => ['id' => $data["rest_org_id"]],
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
                                        <div class="col-sm-3">
                                            <?= Html::label('Ресторан', 'filter_restaurant', ['class' => 'label filter_catalog', 'style' => 'color:#555']) ?>
                                            <?= Html::dropDownList('filter_restaurant', null, $arr_restaurant, ['prompt' => 'Все', 'class' => 'form-control', 'id' => 'filter_restaurant'])
                                            ?> 
                                        </div>
                                        <div class="col-sm-3">
                                            <?= Html::label('Каталог', 'filter_catalog', ['class' => 'label filter_catalog', 'style' => 'color:#555']) ?>
                                            <?= Html::dropDownList('filter_catalog', null, $arr_catalog, ['prompt' => 'Все', 'class' => 'form-control', 'id' => 'filter_catalog'])
                                            ?>  
                                        </div>
                                        <div class="col-sm-3">
                                            <?= Html::label('Статус', 'filter_invite', ['class' => 'label filter_invite', 'style' => 'color:#555']) ?>
                                            <?=
                                            Html::dropDownList('filter_invite', null, [
                                                '0' => 'Не подтвержден',
                                                '1' => 'Подтвержден',
                                                    ], ['prompt' => 'Все', 'class' => 'form-control', 'id' => 'filter_invite'])
                                            ?> 
                                        </div>
                                        <div class="col-sm-3 col-md-2 col-lg-1">
                                            <?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
                                            <?= Html::button('<i class="fa fa-times" aria-hidden="true"></i>', ['class' => 'form-control clear_filters btn btn-outline-danger teaser']) ?>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <?php Pjax::begin(['enablePushState' => false, 'id' => 'cl-list',]); ?>
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
                        $customJs = <<< JS
$('#filter_restaurant').on("change", function () {
       $('#filter_catalog').val(''), 
       $('#filter_invite').val('')
       $.pjax({
        type: 'GET',
        url: 'index.php?r=vendor/clients',
        container: '#cl-list',
        push: false,
        data: { 
            filter_restaurant: $('#filter_restaurant').val(), 
            filter_catalog: $('#filter_catalog').val(), 
            filter_invite: $('#filter_invite').val() 
              }
      })
});
$('#filter_catalog').on("change", function () {
        $('#filter_restaurant').val(''), 
       $('#filter_invite').val('')
       $.pjax({
        type: 'GET',
        push: false,
        url: 'index.php?r=vendor/clients',
        container: '#cl-list',
        data: { 
            filter_restaurant: $('#filter_restaurant').val(), 
            filter_catalog: $('#filter_catalog').val(), 
            filter_invite: $('#filter_invite').val() 
              }
      })
});
$('#filter_invite').on("change", function () {
       $('#filter_restaurant').val(''), 
       $('#filter_catalog').val('')
       $.pjax({
        type: 'GET',
        push: false,
        url: 'index.php?r=vendor/clients',
        container: '#cl-list',
        data: { 
            filter_restaurant: $('#filter_restaurant').val(), 
            filter_catalog: $('#filter_catalog').val(), 
            filter_invite: $('#filter_invite').val()
              }
      })
});
$('.clear_filters').on("click", function () {
       $('#filter_restaurant').val(''), 
       $('#filter_catalog').val(''), 
       $('#filter_invite').val('')
       $.pjax({
        type: 'GET',
        push: false,
        url: 'index.php?r=vendor/clients',
        container: '#cl-list',
        data: { 
            filter_restaurant: $('#filter_restaurant').val(), 
            filter_catalog: $('#filter_catalog').val(), 
            filter_invite: $('#filter_invite').val()
              }
      })
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
$('input[type=checkbox]').live('change', function(e) {
    var id = $(this).attr('data-id');
    var elem = $(this).attr('name').substr(0, 9);
    var state = $(this).prop("checked");
        console.log(elem)
if(elem=="restOrgId"){invite(elem,state,id);}
function invite(elem,state,id){
		$.ajax({
	        url: "index.php?r=vendor/ajax-invite-rest-org-id",
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
	        url: "index.php?r=vendor/remove-client",
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