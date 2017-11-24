<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use yii\widgets\Pjax;
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
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.vendor.catalog_five', ['ru'=>'Каталог']) ?></h4>
</div>
<div class="modal-body">
<?php 
$gridColumnsCatalog = [
    [
    'label'=>Yii::t('message', 'frontend.views.vendor.art_four', ['ru'=>'Артикул']),
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->article;},
    ],
    [
    'label'=>Yii::t('message', 'frontend.views.vendor.product_two', ['ru'=>'Продукт']),
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->product;},
    ],
    [
    'label'=>Yii::t('message', 'frontend.views.vendor.multiplicity_two', ['ru'=>'Кратность']),
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->units;},
    ],
    [
    'label'=>Yii::t('message', 'frontend.views.vendor.base_price', ['ru'=>'Базовая цена']),
    'value'=>function ($data) { 
    $price = common\models\CatalogBaseGoods::find()->where(['id'=>$data->base_goods_id])->one()->price;
    return $price.Yii::t('message', 'frontend.views.vendor.rouble', ['ru'=>" руб."]);
    },
    ],
    [
    'label'=>Yii::t('message', 'frontend.views.vendor.cat_price', ['ru'=>'Цена каталога']),
    'value'=>function ($data) {
    return $data->price.Yii::t('message', 'frontend.views.vendor.rouble_two', ['ru'=>" руб."]);
    },
    ],
    [
    'attribute' => Yii::t('message', 'frontend.views.vendor.in_stock_two', ['ru'=>'Наличие']),
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;vertical-align:middle'],
    'value'=>function ($data) {common\models\CatalogBaseGoods::get_value($data->base_goods_id)->status==common\models\CatalogBaseGoods::STATUS_OFF?
            $product_status='<span class="text-danger">' . Yii::t('message', 'frontend.views.vendor.nope_two', ['ru'=>'Нет']) . ' </span>':
            $product_status='<span class="text-success">' . Yii::t('message', 'frontend.views.vendor.yep_two', ['ru'=>'Есть']) . ' </span>';
            return $product_status;
        },
    ],  
];
?>
<div class="box-body table-responsive no-padding">
<?php Pjax::begin(['enablePushState' => false,'timeout' => 10000, 'id' => 'catalog-list'])?>
<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsCatalog, 
        'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
        'bordered' => false,
        'striped' => true,
        'condensed' => false,
        'responsive' => false,
        'hover' => false,
           'resizableColumns'=>false,
    
]);
?> 
<?php Pjax::end(); ?> 
</div>   
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.vendor.close_two', ['ru'=>'Закрыть']) ?></a>
</div>
<?php ActiveForm::end(); ?>
