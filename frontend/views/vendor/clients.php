<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use dosamigos\switchinput\SwitchBox;
//use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
//use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Users;
//use common\models\CatalogBaseGoods;
//use common\models\CatalogGoods;
?>
<?php Pjax::begin(['enablePushState' => false, 'id' => 'clients-list',]); ?>
<?php 
$gridColumnsClients = [
		[
		'label'=>'ресторан',
		'value'=>'rest_org_id',
		],
                [
		'label'=>'Статус',
		'value'=>'invite',
		],
                [
		'label'=>'Каталог назначеный',
		'value'=>'cat_id',
		],
                [
                'attribute' => 'Статус сотрудничества',
                'format' => 'raw',
                'contentOptions' => ['style' => 'width:50px;'],
                'value' => function ($data) {
                    $link = SwitchBox::widget([
                        'name' => 'restOrgId_'.$data->rest_org_id,
                        'checked' => $data->invite==1 ? true : false,
                        'clientOptions' => [
                            'onColor' => 'success',
                            'offColor' => 'default',
                            'onText'=>'Да',
                            'offText'=>'Нет',
                            'baseClass'=>'bootstrap-switch',
                            ],
                        ]);
                    return $link;
                    },
                ],
    
];
?>
<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsClients,
]);
?> 
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
$('input[type=checkbox]').live('switchChange.bootstrapSwitch', function (event, state) {	
var elem,e,id,state
elem = $(this).attr('name').substr(0, 9);
e = $(this).attr('name')
console.log(elem); 
if(elem=="restOrgId"){id = e.replace('restOrgId_',''); invite(elem,state,id);}
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
JS;
$this->registerJs($customJs, View::POS_READY);
?>