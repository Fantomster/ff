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
            'id' => 'view-catalog'
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.client.supp.catalog_two', ['ru'=>'Каталог']) ?></h4>
</div>
<div class="modal-body">
    <?php
    $gridColumnsCatalog = [
        [
            'label' => Yii::t('message', 'frontend.views.client.supp.art_two', ['ru'=>'Артикул']),
            'value' => function ($data) {
                return $data['article'];
            },
        ],
        [
            'label' => Yii::t('message', 'frontend.views.client.supp.good_name_two', ['ru'=>'Наименование товара']),
            'format' => 'raw',
            'value' => function ($data) {
                return Html::decode(Html::decode($data['product']));
            },
            'noWrap' => false,
            'contentOptions' =>
            ['style' => 'max-width: 350px; overflow: auto; word-wrap: break-word;'],
        ],
        [
            'label' => Yii::t('message', 'frontend.views.client.supp.frequency_two', ['ru'=>'Кратность']),
            'value' => function ($data) {
                return $data['units'];
            },
        ],
        [
            'label' => Yii::t('message', 'frontend.views.client.supp.price_two', ['ru'=>'Цена']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['price'] . " " . $data['symbol'];
            },
        ],
        [
            'label' => Yii::t('message', 'frontend.views.client.supp.measure', ['ru'=>'Единица измерения']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['ed'];
            },
        ],
        [
            'attribute' => Yii::t('message', 'frontend.views.client.supp.in_stock_two', ['ru'=>'Наличие']),
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;vertical-align:middle'],
            'value' => function ($data) {
                return $data['status'] == common\models\CatalogBaseGoods::STATUS_OFF ?
                        '<div class="label label-table label-danger">' . Yii::t('message', 'frontend.views.client.supp.nope_two', ['ru'=>'Нет']) . ' </div>' :
                        '<div class="label label-table label-success">' . Yii::t('message', 'frontend.views.client.supp.yep_two', ['ru'=>'Есть']) . ' </div>';
                return $product_status;
            },
        ],
    ];
    ?>
    <div class="box-body table-responsive no-padding">
        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'pjax-catalog-list']) ?>
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'filterPosition' => false,
            'columns' => $gridColumnsCatalog,
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
            'bordered' => false,
            'striped' => true,
            'summary' => false,
            'condensed' => false,
            'responsive' => false,
            'hover' => false,
            'resizableColumns' => false,
        ]);
        ?> 
<?php Pjax::end(); ?> 
    </div>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close_three', ['ru'=>'Закрыть']) ?></a>
</div>
<?php ActiveForm::end(); ?>
