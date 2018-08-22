<?php

use dosamigos\fileupload\FileUpload;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\helpers\Json;
use common\models\Currency;
use yii\helpers\Url;

$this->title = Yii::t('message', 'frontend.views.vendor.main_catalog_seven', ['ru' => 'Главный каталог']);

?>

<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.vendor.creating_of_main', ['ru' => 'Создание главного каталога']) ?>
        <small></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('message', 'frontend.views.vendor.new_cat_create', ['ru' => 'Создание главного каталога'])
        ],
    ])
    ?>
</section>

<section class="content">
    <?=
    FileUpload::widget([
//    'model' => $model,
//    'attribute' => 'image',
        'name' => 'catalogFile',
        'url' => Yii::$app->urlManagerWebApi->createAbsoluteUrl(["/vendor/reset"]), 
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
                                    data.context = $('<p/>').text('Uploading...').appendTo($('#files'));
                                    console.log(e);
                                    console.log(data);
                                    var reader = new FileReader();
                                    reader.readAsDataURL(data.files[0]);
                                    reader.onload = function () {
                                        console.log(reader.result);
                                    };
                                    //data.submit();
                                }",
            'fileuploaddone' => "function (e, data) {
                                    data.context.text(data.result.files[0].name);
                                    console.log(e);
                                    console.log(data);
                                }"
        ],
    ]);
    ?>
    <div id="files" class="files"></div>
</section>