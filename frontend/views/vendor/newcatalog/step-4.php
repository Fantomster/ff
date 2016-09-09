<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use dosamigos\switchinput\SwitchBox;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Users;
?>
<?php Pjax::begin(['id' => 'pjax-container']); ?>
    <?= Html::a(
        'Завершить',
        ['vendor/catalogs'],
        ['class' => 'btn btn-success','style' => 'float:right;margin-left:10px;']
    ) ?>
    <?= Html::a(
        'Вернуться на шаг 3',
        ['vendor/step-3','id'=>$cat_id],
        ['class' => 'btn btn-default','style' => 'float:right;margin-left:10px;']
    ) 
    ?>
    <h2>Подпишите участников на этот каталог</h2>
<?php 
$gridColumns = [
		[
		'label'=>'Ресторан',
		'value'=>function ($data) {
		return $data->rest_org_id;
		}
		//'rest_org_id',
		],
		[
		'label'=>'Текущий каталог',
		'value'=>function ($data) {
		$cat_id = $data->cat_id==0 ? 'Не назначен' : $data->cat_id;
		return $cat_id;
		}
		
		//'cat_id',
		],
        [
            'attribute' => 'Назначить каталог',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = SwitchBox::widget([
					    'name' => 'setcatalog_'.$data->rest_org_id,
					    'checked' => $data->status==1 && $data->cat_id ==Yii::$app->request->get('id') ? true : false,
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
    'columns' => $gridColumns,
    'resizableColumns'=>false,
    'containerOptions' => ['style'=>'overflow: auto'], // only set when $responsive = false
    'headerRowOptions'=>['class'=>'kartik-sheet-style'],
    'filterRowOptions'=>['class'=>'kartik-sheet-style'],
    'pjax' => true, 
    'pjaxSettings' =>
        [
            'neverTimeout'=>true,
            'options'=>['id'=>'w0'],
        ], 
]);
?>
<?php
$this->registerJs('
/** 
 * Forward port jQuery.live()
 * Wrapper for newer jQuery.on()
 * Uses optimized selector context 
 * Only add if live() not already existing.
*/
if (typeof jQuery.fn.live == "undefined" || !(jQuery.isFunction(jQuery.fn.live))) {
  jQuery.fn.extend({
      live: function (event, callback) {
         if (this.selector) {
              jQuery(document).on(event, this.selector, callback);
          }
      }
  });
}

$("input[type=checkbox]").on("switchChange.bootstrapSwitch", function (event, state) {	
var e,id,state
elem = $(this);
e = $(this).attr("name")
id = e.replace("setcatalog_","")
  //bootbox.confirm("<h3>Подтвердите действие</h3>", function(result) {if(result){
    $.ajax({
    url: "index.php?r=vendor/step-4&id='. $cat_id .'",
    type: "POST",
    dataType: "json",
    data: {"add-client":true,"rest_org_id":id,"state":state},
    cache: false,
    success: function(response) {
        console.log(response);
        $.pjax.reload({container: "#pjax-container"});
        },
        failure: function(errMsg) {
        console.log(errMsg);
        }
    });
    //}else{elem.bootstrapSwitch("toggleState" , false);}
  //})
})
');
?>
<?php Pjax::end(); ?>