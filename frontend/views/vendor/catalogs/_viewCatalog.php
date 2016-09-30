<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
?>
<?php
$form = ActiveForm::begin([
    'id' => 'view-catalog',
    'enableAjaxValidation' => false,
    'action' => Url::toRoute(['vendor/view-catalog', 'id' => $cat_id]),
    'options' => [
        'class' => 'view-catalog',
    ],
]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Каталог</h4>
</div>
<div class="modal-body">
<?php 
$gridColumnsCatalog = [
    [
    'label'=>'Артикул',
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->article;},
    ],
    [
    'label'=>'Продукт',
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->product;},
    ],
    [
    'label'=>'Кратность',
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->units;},
    ],
    [
    'label'=>'Базовая цена',
    'value'=>function ($data) { 
    $price = common\models\CatalogBaseGoods::find()->where(['id'=>$data->base_goods_id])->one()->price;
    return $price." руб.";
    },
    ],
    [
    'label'=>'Цена каталога',
    'value'=>function ($data) {
    return $data->price." руб.";
    },
    ],
    [
    'attribute' => 'Наличие',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;vertical-align:middle'],
    'value'=>function ($data) {common\models\CatalogBaseGoods::get_value($data->base_goods_id)->status==common\models\CatalogBaseGoods::STATUS_OFF?
            $product_status='<span class="text-danger">Нет</span>':
            $product_status='<span class="text-success">Есть</span>';
            return $product_status;
        },
    ],  
];
?>
<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsCatalog
]);
?>     
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-default" data-dismiss="modal">Закрыть</a>
</div>
<?php ActiveForm::end(); ?>
