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
use kartik\checkbox\CheckboxX;
$this->registerCss('.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}');
$this->title = 'Назначить каталог';
?>
<div class="panel-body">
    <h3 class="font-light"><i class="fa fa-list-alt"></i> Редактирование каталога <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?></h3>
</div>
<div class="panel-body">
    <ul class="nav nav-tabs">
      <?='<li>'.Html::a('Имя каталога',['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
      <?='<li>'.Html::a('Добавить продукты',['vendor/step-2','id'=>$cat_id]).'</li>'?>
      <?='<li>'.Html::a('Редактировать',['vendor/step-3','id'=>$cat_id]).'</li>'?>
      <?='<li class="active">'.Html::a('Назначить',['vendor/step-4','id'=>$cat_id]).'</li>'?>
    </ul>
</div>
<?php Pjax::begin(['id' => 'pjax-container']); ?>
<?php 
$gridColumns = [
		[
		'label'=>'Ресторан',
		'value'=>function ($data) {
                $organization_name=common\models\Organization::get_value($data->rest_org_id)->name;
                return $organization_name;
                }
		],
		[
		'label'=>'Текущий каталог',
                'format' => 'raw',
		'value'=>function ($data) {
		$catalog_name = $data->cat_id==0 ? '' : 
                common\models\Catalog::get_value($data->cat_id)->name;
		return $catalog_name;
		}
		],
              [
            'attribute' => 'Назначить',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = CheckboxX::widget([
                    'name'=>'setcatalog_'.$data->rest_org_id,
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>$data->status==1 && $data->cat_id ==Yii::$app->request->get('id') ? 1 : 0,
                    'autoLabel' => true,
                    'options'=>['id'=>'setcatalog_'.$data->id, 'data-id'=>$data->rest_org_id],
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
</div>
<?php Pjax::end(); ?>
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

$("input[type=checkbox]").on("change", function(e) {	
var id = $(this).attr("data-id");
var state = $(this).prop("checked");
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
})
');
?>
