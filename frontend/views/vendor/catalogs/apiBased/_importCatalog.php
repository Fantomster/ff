<?php

use dosamigos\fileupload\FileUpload;

?>
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