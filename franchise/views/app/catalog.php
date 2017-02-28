<?php

use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;
use kartik\editable\Editable;
use yii\helpers\Html;
use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Category;
use common\models\CatalogBaseGoods;
use kartik\checkbox\CheckboxX;
use common\assets\CroppieAsset;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);
?>
<?php
$this->registerJs("           
   // var uploadCrop;

function readFile(input) {
        if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
                        $('.upload-avatar').addClass('ready');
                        $('.upload-demo-wrap').css('opacity','1').css('z-index','198');
                        $('.upload-block').css('padding-bottom','44px');
                        console.log('ok');
        uploadCrop.croppie('bind', {
                url: e.target.result
        }).then(function(){
                console.log('jQuery bind complete');
        });

    }

    reader.readAsDataURL(input.files[0]);
}
else {
        swal('Sorry - your browser does not support the FileReader API');
    }
}
$(document).on('change', '#upload', function () { 
    size = $('#upload').get(0).files[0].size;
    if (size <= 2097152) {
        readFile(this); 
    }
});"
);
?>
<?php
$this->title = 'Каталог №' . $id;

$this->registerCss('');
?>
<?=
Modal::widget([
    'id' => 'add-edit-product',
    'clientOptions' => false,
])
?>
<?php
Modal::begin([
    'id' => 'add-product-market-place',
    'clientOptions' => false,
    'size' => 'modal-lg',
]);
Modal::end();
?>
<?php
$grid = [
    [
        'attribute' => 'article',
        'label' => 'Артикул',
        'value' => 'article',
        'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
        'attribute' => 'product',
        'label' => 'Наименование',
        'value' => 'product',
        'contentOptions' => ['style' => 'vertical-align:middle;width:20%'],
    ],
    [
        'attribute' => 'units',
        'label' => 'Кратность',
        'value' => function ($data) {
            return empty($data['units']) ? '' : $data['units'];
        },
        'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
        'attribute' => 'category_id',
        'label' => 'Категория',
        'value' => function ($data) {
            $data['category_id'] == 0 ? $category_name = '' : 
                $category_name = \common\models\MpCategory::find()->where(['id' => $data['category_id']])->one()->name;
            return $category_name;
        },
                'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
        'attribute' => 'price',
        'label' => 'Цена',
        'value' => 'price',
        'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
        'attribute' => 'ed',
        'label' => 'Ед. измерения',
        'value' => function ($data) {
            return $data['ed'];
        },
        'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
        'attribute' => 'status',
        'label' => 'Наличие',
        'format' => 'raw',
        'contentOptions' => ['style' => 'vertical-align:middle;'],
        'value' => function ($data) {
        $link = CheckboxX::widget([
                'name' => 'status_' . $data['id'],
                'initInputType' => CheckboxX::INPUT_CHECKBOX,
                'value' => $data['status'] == 0 ? 0 : 1,
                'autoLabel' => true,
                'options' => [
                    'id' => 'status_' . $data['id'], 
                    'data-id' => $data['id'], 
                    'event-type' => 'set-status'
                    ],
                'pluginOptions' => [
                    'threeState' => false,
                    'theme' => 'krajee-flatblue',
                    'enclosedLabel' => true,
                    'size' => 'lg',
                ]
        ]);
        return $link;
        },
    ],
    [
        'attribute' => '',
        'label' => '',
        'format' => 'raw',
        'contentOptions' => ['style' => 'width:70px'],
        'headerOptions' => ['class' => 'text-center'],
        'value' => function ($data) {
            $data['market_place'] == 0 ?
                    $link = Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', ['/vendor/ajax-update-product-market-place',
                        'id' => $data['id']], [
                        'data' => [
                            'target' => '#add-product-market-place',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
                        'class' => 'btn btn-sm btn-outline-success'
                    ]) :
                    $link = Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', ['/vendor/ajax-update-product-market-place',
                        'id' => $data['id']], [
                        'data' => [
                            'target' => '#add-product-market-place',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
                        'class' => 'btn btn-sm btn-success'
            ]);
            return $link;
        },
    ],
    [
        'attribute' => '',
        'label' => '',
        'format' => 'raw',
        'contentOptions' => ['style' => 'width:50px;'],
        'value' => function ($data) {
    $link = Html::button('<i class="fa fa-trash m-r-xs"></i>', [
                'class' => 'btn btn-sm btn-danger del-product',
                'data' => ['id' => $data['id']],
            ]);
            return $link;
        },
    ],
];
?> 
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> Каталог № <?=$id?>
        <small></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Каталоги',
                'url' => ['vendor/catalogs'],
            ],
            'Главный каталог',
        ],
    ])
    ?>
</section>
<section class="content">
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-danger alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        <h4><i class="icon fa fa-check"></i>Ошибка</h4>
        <?= Yii::$app->session->getFlash('success') ?>
    </div>
<?php endif; ?>
    <div class="panel-body">
        <div class="box-body no-padding">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-search"></i>
                    </span>
                <?= Html::input('text', 'search', $searchString, ['class' => 'form-control', 'placeholder' => 'Поиск', 'id' => 'search', 'style'=>'width:300px']) ?>
                </div>
                <div class="input-group">
        <?=
        Modal::widget([
            'id' => 'add-product',
            'clientOptions' => ['class' => 'pull-right'],
            'toggleButton' => [
                'label' => '<i class="fa fa-plus-circle"></i> Новый товар',
                'tag' => 'a',
                'data-target' => '#add-product-market-place',
                'class' => 'btn btn-fk-success btn-sm pull-right',
                'href' => Url::to(['/vendor/ajax-create-product-market-place', 'id' => Yii::$app->request->get('id')]),
            ],
        ])
        ?>
                </div>
        </div>
    </div> 
    <div class="panel-body">
        <div class="box-body table-responsive no-padding">
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                'filterPosition' => false,
                'columns' => $grid,
                'options' => ['class' => 'table-responsive'],
                'tableOptions' => ['class' => 'table table-bordered', 'role' => 'grid'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                'bordered' => false,
                'striped' => false,
                'condensed' => false,
                'responsive' => false,
                'hover' => false,
                'resizableColumns' => false,
                'export' => [
                    'fontAwesome' => true,
                ],
            ]);
            ?> 
        </div>
    </div>  
</section>
