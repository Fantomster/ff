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
    <h4 class="modal-title">Каталог</h4>
</div>
<div class="modal-body">
<?php 
$gridColumnsCatalog = [
    [
    'label'=>'Артикул',
    'value'=>'article',
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'label'=>'Продукт',
    'value'=>'product',
    'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
    'label'=>'Кратность',
    'value'=>'units',
    'contentOptions' => ['style' => 'vertical-align:middle'],    
    ],
    [
    'label'=>'Категория',
    'value'=>function ($data) {
    return $data->category_id==0 ? '': common\models\Category::get_value($data->category_id)->name;
    },
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'label'=>'Цена',
    'value'=>function ($data) {return $data->price;},
    'contentOptions' => ['style' => 'vertical-align:middle'],
    ],
    [
    'attribute' => 'Наличие',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:50px;vertical-align:middle'],
    'value'=>function ($data) {$data->status==common\models\CatalogBaseGoods::STATUS_OFF?
            $product_status='<span class="text-danger">Нет</span>':
            $product_status='<span class="text-success">Есть</span>';
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
        'tableOptions' => ['class' => 'table no-margin'],
        'options' => ['class' => 'table-responsive'],
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
    <a href="#" class="btn btn-default" data-dismiss="modal">Закрыть</a>
</div>
<?php ActiveForm::end(); ?>
