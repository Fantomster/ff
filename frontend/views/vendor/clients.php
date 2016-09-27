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
$this->registerCss('.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}');
?>
<?php Pjax::begin(['enablePushState' => false, 'id' => 'clients-list',]); ?>
<?php 
$gridColumnsClients = [
		[
		'label'=>'Ресторан',
		'value'=>function ($data) {
                $organization_name = common\models\Organization::find()->where(['id'=>$data->rest_org_id])->one()->name;
                return $organization_name;
                }
		],
		[
		'label'=>'Текущий каталог',
                'format' => 'raw',
		'value'=>function ($data) {
                $cat = $data->cat_id==0 ? '' : common\models\Catalog::get_value($data->cat_id)->name;

		return $cat;
		}
		],
                [
                'attribute' => 'Статус',
                'format' => 'raw',
                'contentOptions' => ['style' => 'width:50px;'],
                'value' => function ($data) {
                    $link = CheckboxX::widget([
                    'name'=>'restOrgId_'.$data->rest_org_id,
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>$data->invite==0 ? 0 : 1,
                    'autoLabel' => true,
                    'options'=>['id'=>'restOrgId_'.$data->rest_org_id, 'data-id'=>$data->rest_org_id],
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
<div class="panel-body">
    <?=
                Modal::widget([
                    'id' => 'add-client',
                    'clientOptions' => false,
                    'toggleButton' => [
                        'label' => '<i class="fa fa-plus"></i> Пригласить клиента',
                        'tag' => 'a',
                        'data-target' => '#add-client',
                        'class' => 'btn btn-lg btn-info m-t-xs m-r pull-right',
                        'href' => Url::to(['/vendor/ajax-add-client']),
                        'style' => 'float:right',
                    ],
                ])
                ?>
    <h3 class="font-light"><i class="fa fa-users"></i> Мои Клиенты</h3>
</div>
<div class="panel-body">
<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsClients,
]);
?> 
</div>
<?php Pjax::end(); ?> 
<?php
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
		        $.pjax.reload({container: "#clients-list"});
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
JS;
$this->registerJs($customJs, View::POS_READY);
?>