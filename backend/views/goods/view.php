<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use common\assets\CroppieAsset;

CroppieAsset::register($this);

$this->title = Html::decode($model->product);
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Товары'), 'url' => ['index']],
    $this->title . ' #' . $model->id
];
?>

<div class="row">
    <div class="col-sm-12">
        <h1><?= $this->title ?></h1>
    </div>
</div>

<div class="clearfix">
    <?= Html::a('Редактировать', '/goods/ajax-update-product-market-place/' . $model->id, [
        'data-target' => '#add-product-market-place',
        'data-toggle' => 'modal',
        'data-backdrop' => 'static',
        'class' => 'btn btn-success'
    ]) ?>
</div>

<div class="clearfix"><br></div>

<div class="row">
    <div class="col-sm-12">
        <?=
        \yii\widgets\DetailView::widget([
            'model' => $model,
            'attributes' => [
                [
                    'format' => 'raw',
                    'attribute' => 'image',
                    'value' => function ($data) {
                        if (empty($data->imageUrl)) {
                            return null;
                        }

                        return Html::tag('img', '', [
                            'src' => $data->imageUrl,
                            'width' => 176,
                            'height' => 119,
                            'class' => 'avatar'
                        ]);
                    }
                ],
                [
                    'attribute' => 'product',
                    'value' => function ($data) {
                        return Html::decode($data->product);
                    }
                ],
                'article',
                'price',
                'ed',
                'weight',
                'units',
                'category.name',
                'brand',
                'mpRegion.name',
                'vendor.name',
                'rating',
                'note',
                'market_place',
                'mp_show_price',
            ]
        ])
        ?>
    </div>
</div>

<?php
$customJs = <<< JS
function readFile(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.upload-avatar').addClass('ready');
            $('.upload-demo-wrap').css('opacity', '1').css('z-index', '198');
            console.log('ok');
            uploadCrop.croppie('bind', {
                    url: e.target.result
                })
                .then(function() {
                    console.log('jQuery bind complete');
                });
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        swal('Sorry - your browser does not support the FileReader API');
    }
}

$(document).on('change', '#upload', function() {
    readFile(this);
});

$(document).on('click', '.upload-result', function(ev) {
    uploadCrop.croppie('result', {
            type: 'canvas',
            size: 'viewport'
        })
        .then(function(resp) {
            popupResult({
                src: resp
            });
        });
});

$("body").on("hidden.bs.modal", "#add-product-market-place", function() {
    location.reload();
});

$("#add-product-market-place").on("click", ".edit", function() {
    var form = $("#marketplace-product-form");
    $('#loader-show').showLoading();
    $.post(form.attr("action"), form.serialize())
        .done(function(result) {
            $('#loader-show').hideLoading();
            form.replaceWith(result);
        });
    return false;
});
JS;
$this->registerJs($customJs, \yii\web\View::POS_READY);

Modal::begin([
    'id' => 'add-product-market-place',
    'clientOptions' => false,
    'size' => 'modal-lg',
]);
Modal::end();
?>
