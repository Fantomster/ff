<?php

use dosamigos\fileupload\FileUpload;

frontend\assets\SlickCarouselAsset::register($this);

$url = Yii::$app->urlManagerWebApi->createAbsoluteUrl(["/vendor/upload-main-catalog"]);

?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.vendor.file_import', ['ru' => 'Импорт из файла']) ?></h4>
</div>
<div class="modal-body">
    <?=
    FileUpload::widget([
//    'model' => $model,
//    'attribute' => 'image',
        'name' => 'catalogFile',
        'url' => $url,
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
                                        var base64 = reader.result;
                                        $.post(
                                            '$url',
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
                                        });
                                    };
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
</div>