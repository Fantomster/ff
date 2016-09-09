<?php
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Users;
use dosamigos\switchinput\SwitchBox;

?>
<?php Pjax::begin(['id' => 'pjax-container'])?>

    <?= Html::a(
        'Перейти на шаг 3',
        ['vendor/step-3'],
        ['class' => 'btn btn-success step-3','style' => 'float:right;margin-left:10px;']
    ) 
    ?>
    <?= Html::a(
        'Вернуться на шаг 1',
        ['vendor/step-1-update','id'=>$cat_id],
        ['class' => 'btn btn-default step-1','style' => 'float:right;margin-left:10px;']
    ) 
    ?>
    <h2>Добавьте продукты</h2>
<?php 
$gridColumnsBaseCatalog = [
    [
    'label'=>'Артикул',
    'value'=>'article',
    ],
    [
    'label'=>'Продукт',
    'value'=>'product',
    ],
    [
    'label'=>'кол-во',
    'value'=>'units',
    ],
    [
    'label'=>'Цена',
    'value'=>'price',
    ],
    [
    'label'=>'Категория',
    'value'=>function ($data,$fff) {return $fff;},
    ],
    [
    'attribute' => 'Добавить в каталог',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;'],
    'value' => function ($data) {
        $link = SwitchBox::widget([
            'name' => 'product_'.$data->id,
            'id'=>'status_'.$data->id,
            'checked' => common\models\CatalogGoods::searchProductFromCatalogGoods($data->id,Yii::$app->request->get('id'))? true : false,
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

    ]
];
?>
<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsBaseCatalog,
        'resizableColumns'=>false,
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
$(".step-3").click(function(e){
e.preventDefault();
$.ajax({
    url: "index.php?r=vendor/step-2&id='. $cat_id .'",
    type: "POST",
    dataType: "json",
    data: {"check":true},
    cache: false,
    success: function(response) {
            if(response.success){
                var url = "' . Url::toRoute(['vendor/step-3','id'=>$cat_id]) . '";
                $.pjax({url: url, container: "#pjax-container"});
                }else{
            console.log(response);    
            }
        },
        failure: function(errMsg) {
        console.log(errMsg);
        }
    });
}); 
$("input[type=checkbox]").live("switchChange.bootstrapSwitch", function (event, state) {	
var e,id,state
e = $(this).attr("name")
id = e.replace("product_","")
$.ajax({
    url: "index.php?r=vendor/step-2&id='. $cat_id .'",
    type: "POST",
    dataType: "json",
    data: {"add-product":true,"baseProductId":id,"state":state},
    cache: false,
    success: function(response) {
                console.log(response);
                
        },
        failure: function(errMsg) {
        console.log(errMsg);
        }
    });
});
');
?>
<?php  Pjax::end(); ?>