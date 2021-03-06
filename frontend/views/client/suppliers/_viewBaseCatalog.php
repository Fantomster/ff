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
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.client.supp.catalog', ['ru'=>'Каталог']) ?></h4>
</div>
<div class="modal-body">
<?php 
$gridColumnsCatalog = [
    [
    'label'=>Yii::t('message', 'frontend.views.client.supp.art', ['ru'=>'Артикул']),
    'value'=>'article',
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'label'=>Yii::t('message', 'frontend.views.client.supp.good_name', ['ru'=>'Наименование товара']),
        'format' => 'raw',
    'value'=>function($data) {
        return Html::decode(Html::decode($data['product']));
    },
    'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
    'label'=>Yii::t('message', 'frontend.views.client.supp.frequency', ['ru'=>'Кратность']),
    'value'=>'units',
    'contentOptions' => ['style' => 'vertical-align:middle'],    
    ],
    [
    'label'=>Yii::t('message', 'frontend.views.client.supp.category', ['ru'=>'Категория']),
    'value'=>function ($data) {
    return $data->category_id==0 ? '': common\models\Category::get_value($data->category_id)->name;
    },
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'label'=>Yii::t('message', 'frontend.views.client.supp.price', ['ru'=>'Цена']),
    'value'=>function ($data) {return $data->price;},
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'attribute' => Yii::t('message', 'frontend.views.client.supp.in_stock', ['ru'=>'Наличие']),
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;vertical-align:middle'],
    'value'=>function ($data) {$data->status==common\models\CatalogBaseGoods::STATUS_OFF?
            $product_status='<span class="text-danger">' . Yii::t('message', 'frontend.views.client.supp.nope', ['ru'=>'Нет']) . ' </span>':
            $product_status='<span class="text-success">' . Yii::t('message', 'frontend.views.client.supp.yep', ['ru'=>'Есть']) . ' </span>';
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
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close_two', ['ru'=>'Закрыть']) ?></a>
</div>
<?php ActiveForm::end(); ?>
