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
    <h4 class="modal-title"><?= Yii::t('app', 'franchise.views.catalog.catalogs.catalog_two', ['ru'=>'Каталог']) ?></h4>
</div>
<div class="modal-body">
<?php 
$gridColumnsCatalog = [
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.art_four', ['ru'=>'Артикул']),
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->article;},
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.product_two', ['ru'=>'Продукт']),
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->product;},
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.multiplicity_three', ['ru'=>'Кратность']),
    'value'=>function ($data) {return common\models\CatalogBaseGoods::get_value($data->base_goods_id)->units;},
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.base_price', ['ru'=>'Базовая цена']),
    'value'=>function ($data) { 
    $price = common\models\CatalogBaseGoods::find()->where(['id'=>$data->base_goods_id])->one()->price;
    return $price.Yii::t('app', 'franchise.views.catalog.catalogs.rouble', ['ru'=>" руб."]);
    },
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.cat_price', ['ru'=>'Цена каталога']),
    'value'=>function ($data) {
    return $data->price.Yii::t('app', 'franchise.views.catalog.catalogs.rouble_two', ['ru'=>" руб."]);
    },
    ],
    [
    'attribute' => Yii::t('app', 'franchise.views.catalog.catalogs.in_stock', ['ru'=>'Наличие']),
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;vertical-align:middle'],
    'value'=>function ($data) {common\models\CatalogBaseGoods::get_value($data->base_goods_id)->status==common\models\CatalogBaseGoods::STATUS_OFF?
            $product_status='<span class="text-danger">' . Yii::t('app', 'franchise.views.catalog.catalogs.nope_two', ['ru'=>'Нет']) . ' </span>':
            $product_status='<span class="text-success">' . Yii::t('app', 'franchise.views.catalog.catalogs.yep_two', ['ru'=>'Есть']) . ' </span>';
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
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('app', 'franchise.views.catalog.catalogs.close_two', ['ru'=>'Закрыть']) ?></a>
</div>
<?php ActiveForm::end(); ?>
