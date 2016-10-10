<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\Pjax;
?>
<?php
$form = ActiveForm::begin([
    'id' => 'view-catalog',
    'enableAjaxValidation' => false,
    'action' => Url::toRoute(['client/view-catalog', 'id' => $cat_id]),
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
    'label'=>'Наименование товара',
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->product;},
    'noWrap' => false,
    //the line below has solved the issue
    'contentOptions' => 
    ['style'=>'max-width: 350px; overflow: auto; word-wrap: break-word;'],
    ],
    [
    'label'=>'Кратность',
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->units;},
    ],
    [
    'label'=>'Цена',
    'value'=>function ($data) {
    return $data->price." руб.";
    },
    ],
    [
    'attribute' => 'Наличие',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;vertical-align:middle'],
    'value'=>function ($data) {common\models\CatalogBaseGoods::get_value($data->base_goods_id)->status==common\models\CatalogBaseGoods::STATUS_OFF?
            $product_status='<div class="label label-table label-danger">Нет</div>':
            $product_status='<div class="label label-table label-success">Есть</div>';
            return $product_status;
        },
    ],  
];
?>
<div class="box-body table-responsive no-padding">
<?php Pjax::begin(['enablePushState' => false,'timeout' => 10000, 'id' => 'pjax-catalog-list'])?>
<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsCatalog, 
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
<div class="modal-footer">
    <a href="#" class="btn btn-default" data-dismiss="modal">Закрыть</a>
</div>
<?php ActiveForm::end(); ?>
