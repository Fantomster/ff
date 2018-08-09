<?php

/* @var $this yii\web\View */
/* @var $first20 array */
/* @var $tempCatalog common\models\CatalogTemp */
/* @var $rows array */
/* @var $currentUser common\models\User */
/* @var $cat_id integer */

use yii\helpers\Html;
use dosamigos\fileupload\FileUpload;

frontend\assets\SlickCarouselAsset::register($this);

$urlUpload = Yii::$app->urlManagerWebApi->createAbsoluteUrl(["/vendor/upload-main-catalog"]);
$urlDelete = Yii::$app->urlManagerWebApi->createAbsoluteUrl(["/vendor/delete-temp-main-catalog"]);

$this->registerCss('
        .select2-container .select2-selection--single .select2-selection__rendered {margin-top: 0px;}
        .modal-footer {background: #BAB9B9;}
        .arrows-container {position:relative; margin-left:20px;margin-right:20px;}
');

$rows = json_encode($first20, JSON_UNESCAPED_UNICODE + JSON_FORCE_OBJECT);
$tempCatalogExists = empty($tempCatalog) ? 0 : 1;
$mapping = empty($tempCatalog->mapping) ? json_encode([]) : $tempCatalog->mapping;

$this->registerJs("

var userToken = '{$currentUser->access_token}';
var tempCatalogExists = {$tempCatalogExists};
var rows = $.parseJSON('{$rows}');
var mapping = $.parseJSON('{$mapping}');

$(document).on('click','#btn_cat_cancel', function(e) {
    $.post(
        '" . $urlDelete . "',
        {
            'user': {
                language: 'RU',
                token: userToken,
            },
            'request': {
                cat_id: {$cat_id},
            }
        }
    );
    return true;
});

$('.slick-container').slick({
    infinite: false,
    slidesToShow: 1,
    adaptiveHeight: true,
    appendArrows: '.arrows-container',
    initialSlide: tempCatalogExists
});

if (!tempCatalogExists) {
    $('.slick-arrow').hide();
}
");
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.vendor.file_import', ['ru' => 'Импорт из файла']) ?></h4>
</div>
<div class="modal-body">
    <div class="slick-container">
        <div class="step-1">
            <div class="row">
                <div class="col-md-12">
                    <?=
                    FileUpload::widget([
                        'name' => 'catalogFile',
                        'url' => $urlUpload,
                        'options' => ['accept' => 'file/*.xlsx'],
                        'clientOptions' => [
                            'maxFileSize' => 2000000,
                            'autoUpload' => false,
                            'maxNumberOfFiles' => 1,
                        ],
                        'clientEvents' => [
                            'fileuploadfail' => 'function(e, data) {
                                    console.log(e);
                                    console.log(data);
                                }',
                            'fileuploadadd' => "function (e, data) {
                                    //data.context = $('<p/>').text('Uploading...').appendTo($('#files'));
                                    console.log(e);
                                    console.log(data);
                                    var reader = new FileReader();
                                    reader.readAsDataURL(data.files[0]);
                                    reader.onload = function () {
                                        var base64 = reader.result;
                                        $.post(
                                            '$urlUpload',
                                            {
                                                'user': {
                                                    language: 'RU',
                                                    token: '{$currentUser->access_token}'
                                                },
                                                'request': {
                                                    cat_id: {$cat_id},
                                                    data: base64 
                                                }
                                            }
                                        ).done(function (response) {
                                            console.log(JSON.stringify(response));
                                            $('.step-2 .wtf').html(JSON.stringify(response));
                                            $('.slick-container').slick('slickNext')
                                            $('.slick-arrow').show();
                                        });
                                    };
                                }",
                            'fileuploaddone' => "function (e, data) {
                                    //data.context.text(data.result.files[0].name);
                                    console.log(e);
                                    console.log(data);
                                }"
                        ],
                    ]);
                    ?>
                    <div id="files" class="files"></div>
                </div>
            </div>
        </div>
        <div class="step-2">
            <div class="row">
                <div class="col-md-12">
                    <div class="wtf"><?= $rows ?></div>
                    <div class="wtf2"><?= $mapping ?></div>
                </div>
            </div>
        </div>
        <div class="step-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="wtf"><?= $rows ?></div>
                    <div class="wtf2"><?= $mapping ?></div>
                </div>
            </div>
        </div>
        <div class="step-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="wtf"><?= $rows ?></div>
                    <div class="wtf2"><?= $mapping ?></div>
                </div>
            </div>
        </div>
        <div class="step-5">
            <div class="row">
                <div class="col-md-12">
                    <div class="wtf"><?= $rows ?></div>
                    <div class="wtf2"><?= $mapping ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="arrows-container">
        <?=
        Html::a(
                '<i class="fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.vendor.cancel_seven', ['ru' => 'Отмена']), '#', ['class' => 'btn btn-gray', 'data-dismiss' => 'modal',
            'id' => 'btn_cat_cancel',
            'style' => 'margin-bottom:5px;'])
        ?>
    </div>
</div>