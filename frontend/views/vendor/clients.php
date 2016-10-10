<?php
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
		'label'=>'Ресторан',
                'format' => 'raw',
		'value'=>function ($data) {
                $res = common\models\Organization::find()->where(['id'=>$data['rest_org_id']])->one()->name;
                return Html::a(Html::encode($res), ['vendor/view-client', 'id' => $data['rest_org_id']], [
                    'data' => [
                    'target' => '#view-client',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                              ],
                    ]);
                }
		],
                [
		'label'=>'Последний заказ',
		'value'=>function ($data) {
                $res = common\models\Order::find()->select('MAX(CAST(created_at AS CHAR)) as created_at')->where(['client_id'=>$data['rest_org_id'],'vendor_id'=>common\models\User::findIdentity(Yii::$app->user->id)->organization_id])->one()->created_at;
                return $res;
                }
		],
		[
		'label'=>'Текущий каталог',
                'format' => 'raw',
		'value'=>function ($data) {
                $cat = common\models\Catalog::find()->where(['id'=>$data['cat_id']])->one();
                return $data['cat_id']==0? '':
                        Html::a(Html::encode($cat->name), ['vendor/view-catalog', 'id' => $data['cat_id']], [
                    'data' => [
                    'target' => '#view-catalog',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                              ],
                    ]);
		}
		],
                [
                'attribute' => 'Статус сотрудничества',
                'format' => 'raw',
                'contentOptions' => ['style' => 'width:190px;text-align:center'],
                'value' => function ($data) {
                    $link = CheckboxX::widget([
                    'name'=>'restOrgId_'.$data['rest_org_id'],
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>$data['invite']==0 ? 0 : 1,
                    'autoLabel' => true,
                    'options'=>['id'=>'restOrgId_'.$data['rest_org_id'], 'data-id'=>$data['rest_org_id']],
                    'pluginOptions'=>[
                        'threeState'=>false,
                        'theme' => 'krajee-flatblue',
                        'enclosedLabel' => true,
                        'size'=>'lg',
                        ]
                ]);
                return $link;
                    },
                ],
    
];
?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Мои Клиенты</h3>
        <?=
        Modal::widget([
            'id' => 'add-client',
            'clientOptions' => false,
            'toggleButton' => [
                'label' => 'Пригласить клиента',
                'tag' => 'a',
                'data-target' => '#add-client',
                'class' => 'btn btn-sm btn-fk-success pull-right',
                'href' => Url::to(['/vendor/ajax-add-client']),
                'style' => 'float:right',
            ],
        ])
        ?>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
            <div class="panel-body">
                <div class="col-sm-3">
                    <?= Html::label('Ресторан', 'filter_restaurant', ['class' => 'label filter_catalog','style'=>'color:#555']) ?>
                    <?= Html::dropDownList('filter_restaurant', null,
                            $arr_restaurant,['prompt' => '','class' => 'form-control','id'=>'filter_restaurant']) ?> 
                </div>
                <div class="col-sm-3">
                    <?= Html::label('Каталог', 'filter_catalog', ['class' => 'label filter_catalog','style'=>'color:#555']) ?>
                    <?= Html::dropDownList('filter_catalog', null,
                            $arr_catalog,['prompt' => '','class' => 'form-control','id'=>'filter_catalog']) ?>  
                </div>
                <div class="col-sm-3">
                    <?= Html::label('Статус', 'filter_invite', ['class' => 'label filter_invite','style'=>'color:#555']) ?>
                    <?= Html::dropDownList('filter_invite', null,
                            [
                                '0' => 'Не подтвержден',
                                '1' => 'Подтвержден',
                            ],['prompt' => '','class' => 'form-control','id'=>'filter_invite']) ?> 
                </div>
                <div class="col-sm-3">
                    <?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
                    <?= Html::button('Сбросить фильтр', ['class' => 'form-control clear_filters btn btn-primary teaser']) ?>
                </div>
            </div>
        <div class="panel-body">
            <?php Pjax::begin(['enablePushState' => false, 'id' => 'cl-list',]); ?>
            <?=GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterPosition' => false,
                    'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
                    'columns' => $gridColumnsClients, 
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
JS;
$this->registerJs($customJs, View::POS_READY);
?>