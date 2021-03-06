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
use kartik\export\ExportMenu;
use kartik\editable\Editable;
$this->registerCss('.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}');
$this->title = Yii::t('app', 'franchise.views.catalog.newcatalog.edit_products', ['ru'=>'Редактировать продукты']);
?>


<div class="panel-body">
    <h3 class="font-light"><i class="fa fa-list-alt"></i> <?= Yii::t('app', 'franchise.views.catalog.newcatalog.editing_cat_two', ['ru'=>'Редактирование каталога']) ?> <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?></h3>
</div>
<div class="panel-body">
    <ul class="nav nav-tabs">
        <?='<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.cat_name', ['ru'=>'Имя каталога']),['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
        <?='<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.add_goods_five', ['ru'=>'Добавить продукты']),['vendor/step-2','id'=>$cat_id]).'</li>'?>
        <?='<li class="active">'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.edit', ['ru'=>'Редактировать']),['vendor/step-3','id'=>$cat_id]).'</li>'?>
        <?='<li>'.Html::a('test',['vendor/step-3-copy','id'=>$cat_id]).'</li>'?>
        <?='<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.settle_to_rest_three', ['ru'=>'Назначить ресторану']),['vendor/step-4','id'=>$cat_id]).'</li>'?>
    </ul>
</div>
<?php Pjax::begin(['id' => 'pjax-container']); ?>
<?php 
$gridColumnsCatalog = [
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.art_two', ['ru'=>'Артикул']),
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->article;},
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.product', ['ru'=>'Продукт']),
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->product;},
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.base_price', ['ru'=>'Базовая цена']),
    'value'=>function ($data) { 
    $price = common\models\CatalogBaseGoods::find()->where(['id'=>$data->base_goods_id])->one()->price;
    return $price.Yii::t('app', 'franchise.views.catalog.newcatalog.rouble_two', ['ru'=>" руб."]);
    },
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.price_two', ['ru'=>'Цена']),
    'value'=>function ($data) {
    return $data->price.Yii::t('app', 'franchise.views.catalog.newcatalog.rouble_three', ['ru'=>" руб."]);
    },
    ],
    /*[
    'label'=>'Цена',
    'format' => 'raw',
    'value'=>function ($data) {
    return Editable::widget([
        'id'=>'price'.$data->id,
        'name'=>'price', 
        'asPopover' => false,
        'value' => $data->price,
        'size'=>'md',
        'options' => ['style'=>'width:100px']
    ]);
    },
    ],*/
    
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.discount', ['ru'=>'Скидка (руб)']),
    'value'=>function ($data) {return $data->discount!=0?$data->discount.Yii::t('app', 'franchise.views.catalog.newcatalog.rouble_four', ['ru'=>" руб."]):Yii::t('app', 'franchise.views.catalog.newcatalog.zero', ['ru'=>'0 руб.']);},
    ],
    [
    'header' => Yii::t('app', 'franchise.views.catalog.newcatalog.discount_two', ['ru'=>'Скидка (%)']).Html::a('<i class="fa fa-pencil m-r-xs"></i>', ['vendor/ajax-set-percent','id'=>$cat_id], [
            'data' => [
            'target' => '#discount-all-product',
            'toggle' => 'modal',
            'backdrop' => 'static',
                      ],'class'=>'pull-right']),
    'value'=>function ($data) {return $data->discount_percent!=0?$data->discount_percent." %":'0 %';},
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.fix_price', ['ru'=>'Фиксированная цена']),
    'value'=>function ($data) {return $data->discount_fixed!=0?$data->discount_fixed.Yii::t('app', 'franchise.views.catalog.newcatalog.rouble_five', ['ru'=>" руб."]):'-';},
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.total', ['ru'=>'Итоговая']),
    'format' => 'raw',
    'value'=>function ($data) {
        $price = preg_replace('/[^\d.,]/','',$data->price);
        
        if($data->discount==0 && $data->discount_percent==0 && $data->discount_fixed==0){
                $price =  $price;
            }else{
                if($data->discount!=0){
                    $price = $price - $data->discount;
                    $price = $price <= 0 ? 0 : $price;
                }
                if($data->discount_percent!=0){
                    $price = $price - $price/100 * $data->discount_percent;
                }
                if($data->discount_fixed!=0){
                    $price = $data->discount_fixed;
                }
            }
            $price = number_format((float)$price,2, '.', '');
            return '<span class="text-success-fk">'.$price.' ' . Yii::t('app', 'franchise.views.catalog.newcatalog.rouble_six', ['ru'=>'руб.']) . ' </span>';
        },
    ],
    [
    'attribute' => '',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;'],
    'value' => function ($data) {
        $link = Html::a('<i class="fa fa-pencil"></i>', ['/vendor/step3-update-product', 'id' => $data->id], [
            'data' => [
            'target' => '#add-product',
            'toggle' => 'modal',
            'backdrop' => 'static',
                      ],
            'class'=>'btn btn-warning'

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
    'columns' => $gridColumnsCatalog,
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
<?=Modal::widget([
'id' => 'add-product',
'clientOptions' => false,
])
?>
<?=Modal::widget([
'id' => 'discount-all-product',
'clientOptions' => false,
])
?>
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
$(document).on("click", ".set", function() {
var form = $("#set_discount_percent");
$.post(
    form.attr("action"),
        form.serialize()
    ).done(function(result) {
        form.replaceWith(result);
    });
return false;
})
$(".step-4").click(function(e){
e.preventDefault();
var url = "' . Url::toRoute(['vendor/step-4','id'=>$cat_id]) . '";
$.pjax({url: url, container: "#pjax-container"});
});
$(document).on("click", ".edit", function() {
    var form = $("#product-form");
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            form.replaceWith(result);
        });
        return false;
});
$("body").on("hidden.bs.modal", "#add-product,#discount-all-product", function() {
    $(this).data("bs.modal", null);
    $.pjax.reload({container: "#pjax-container"});
})

');
?>
