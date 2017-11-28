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
    'action' => Url::toRoute(['vendor/view-base-catalog', 'id' => $cat_id]),
    'options' => [
        'class' => 'view-catalog',
    ],
]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('app', 'franchise.views.catalog.catalogs.catalog', ['ru'=>'Каталог']) ?></h4>
</div>
<div class="modal-body">
<?php 
$gridColumnsCatalog = [
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.art_three', ['ru'=>'Артикул']),
    'value'=>'article',
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.product', ['ru'=>'Продукт']),
    'value'=>'product',
    'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.multiplicity_two', ['ru'=>'Кратность']),
    'value'=>'units',
    'contentOptions' => ['style' => 'vertical-align:middle'],    
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.category', ['ru'=>'Категория']),
    'value'=>function ($data) {
     $data['category_id']==0 ? $category_name='':$category_name=\common\models\MpCategory::find()->where(['id'=>$data['category_id']])->one()->name;
                            return $category_name;
    },
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'label'=>Yii::t('app', 'franchise.views.catalog.catalogs.prices', ['ru'=>'Цена']),
    'format' => 'raw',
    'value'=>function ($data) {
    return $data->price."<i class=\"fa fa-fw fa-rub\"></i>";
    },
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'attribute' => 'franchise.views.catalog.catalogs.in_stock', ['ru'=>'Наличие'],
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;vertical-align:middle'],
    'value'=>function ($data) {$data->status==common\models\CatalogBaseGoods::STATUS_OFF?
            $product_status='<span class="text-danger">' . Yii::t('app', 'franchise.views.catalog.catalogs.nope', ['ru'=>'Нет']) . ' </span>':
            $product_status='<span class="text-success">' . Yii::t('app', 'franchise.views.catalog.catalogs.yep', ['ru'=>'Есть']) . ' </span>';
            return $product_status;
        },
    ],
    /*[
        'attribute' => 'MarketPlace',
        'value' => 
    ],*/   
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
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('app', 'franchise.views.catalog.catalogs.close', ['ru'=>'Закрыть']) ?></a>
</div>
<?php ActiveForm::end(); ?>
