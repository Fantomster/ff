<?php
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Users;
use dosamigos\switchinput\SwitchBox;
?>
<?php Pjax::begin(['id' => 'pjax-container']); ?>
    <?= Html::a(
        'Перейти на шаг 4',
        ['vendor/step-4'],
        ['class' => 'btn btn-success step-4','style' => 'float:right;margin-left:10px;']
    ) ?>
    <?= Html::a(
        'Вернуться на шаг 2',
        ['vendor/step-2','id'=>$cat_id],
        ['class' => 'btn btn-default','style' => 'float:right;margin-left:10px;']
    ) 
    ?>
    <h2>Отредактируйте продукты</h2>
<?php 
$gridColumnsCatalog = [
    [
    'label'=>'Продукт',
    'value'=>'base_goods_id',
    ],
    [
    'label'=>'Цена',
    'value'=>'price',
    ],
    [
    'label'=>'Скидка (руб)',
    'value'=>'discount',
    ],
    [
    'label'=>'Скидка (%)',
    'value'=>'discount_percent',
    ],
    [
    'label'=>'Фиксированная цена',
    'value'=>'discount_fixed',
    ],
    [
    'label'=>'Итоговая',
    'value'=>'price',
    ],
    [
    'attribute' => '',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;'],
    'value' => function ($data) {
        $link = Html::a('<i class="fa fa-pencil m-r-xs"></i>', ['/vendor/step3-update-product', 'id' => $data->id], [
            'data' => [
            'target' => '#add-product',
            'toggle' => 'modal',
            'backdrop' => 'static',
                      ],
            'class'=>'btn btn-default'

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
<?=Modal::widget([
'id' => 'add-product',
'clientOptions' => false,
])
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
$(".step-4").click(function(e){
e.preventDefault();
var url = "' . Url::toRoute(['vendor/step-4','id'=>$cat_id]) . '";
$.pjax({url: url, container: "#pjax-container"});
});
$(".edit").live("click", function() {
console.log("ol");
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
$("body").on("hidden.bs.modal", "#add-product", function() {
    $(this).data("bs.modal", null);
    $.pjax.reload({container: "#pjax-container"});
})
');
?>
<?php Pjax::end(); ?>