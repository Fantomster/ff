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
use kartik\checkbox\CheckboxX;
$this->registerCss('.table-hover > tbody > tr.select-row:hover > td,.select-row > td {color:#ccc}');
$this->registerCss('.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}');
$this->title = 'Добавить продукты';
?>

<?php Pjax::begin(['id' => 'pjax-container'])?>
<div class="panel-body">
    <h3 class="font-light"><i class="fa fa-list-alt"></i> Редактирование каталога <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?></h3>
</div>
<div class="panel-body">
<ul class="nav nav-tabs">
    <?='<li>'.Html::a('Имя каталога',['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
    <?='<li class="active">'.Html::a('Добавить продукты',['vendor/step-2','id'=>$cat_id]).'</li>'?>
    <?='<li>'.Html::a('Редактировать',['vendor/step-3','id'=>$cat_id]).'</li>'?>
    <?='<li>'.Html::a('Назначить',['vendor/step-4','id'=>$cat_id]).'</li>'?>
</ul>
</div>
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
    'value'=>function ($data) {
    $price = preg_replace('/[^\d.,]/','',$data->price);
    return $price." руб.";
    },
    ],
    [
    'label'=>'Категория',
    'value'=>function ($data) {
                $data->category_id==0 ? $category_name='':$category_name=common\models\Category::get_value($data->category_id)->name;
                return $category_name;
                }
    ],        
    [
    'label'=>'Статус',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;'],    
    'value'=>function ($data) {$data->status==common\models\CatalogBaseGoods::STATUS_OFF?
            $product_status='<i class="fa fa-times" style="color:red"  aria-hidden="true"></i>':
            $product_status='<i class="fa fa-check" style="color:green" aria-hidden="true"></i>';
            return $product_status;
        },
    ],
    [
    'attribute' => 'Добавить',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;'],
    'value' => function ($data) {
        $link = CheckboxX::widget([
                    'name'=>'product_'.$data->id,
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>common\models\CatalogGoods::searchProductFromCatalogGoods($data->id,Yii::$app->request->get('id'))? 1 : 0,
                    'autoLabel' => true,
                    'options'=>['id'=>'product_'.$data->id, 'data-id'=>$data->id],
                    'pluginOptions'=>[
                        'threeState'=>false,
                        'theme' => 'krajee-flatblue',
                        'enclosedLabel' => true,
                        'size'=>'lg',
                        ]
                ]);
                return $link;
        },

    ]
];
?>
<div class="panel-body">
<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsBaseCatalog,
        'resizableColumns'=>false,
        'tableOptions' => [
                //'class' => 'table table-condensed table-bordered',
            ],
        /*'rowOptions'=>function ($data){
            $data->status==common\models\CatalogBaseGoods::STATUS_OFF ?
            $product_style = ['class' => 'select-row'] :
            $product_style = ['class' => 'default'];    
            return $product_style;
        }*/
]);
?>
</div>
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