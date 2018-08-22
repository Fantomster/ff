<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\web\View;

$this->registerCss('.select2-container .select2-selection--single .select2-selection__rendered {margin-top: 0px;}');
$this->registerJs("

$(document).on('click','#btn_cat_cancel', function(e) {
    $.post(
        '".Yii::$app->urlManagerWebApi->createAbsoluteUrl(["/vendor/delete-temp-main-catalog"])."',
        {
            'user': {
                language: 'RU',
                token: '{$currentUser->access_token}'
            },
            'request': {
                cat_id: {$cat_id},
            }
        }
    );
    return true;
});
");
?>
<?php
$form = ActiveForm::begin([
            'id' => 'import-form',
            'enableAjaxValidation' => false,
            'options' => ['enctype' => 'multipart/form-data'],
            'action' => Url::toRoute(['vendor/import', 'id' => Yii::$app->request->get('id')])])
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.vendor.file_import', ['ru'=>'Импорт из файла']) ?></h4>
</div>
<div class="modal-body">

</div>
<div class="modal-footer">
    <div class="row">
        <div class="col-md-12">
    <?= Html::submitButton('<i class="glyphicon glyphicon-import"></i> ' . Yii::t('message', 'frontend.views.vendor.import_three', ['ru'=>'Импорт']) . ' ',
            ['class' => 'btn btn-success import','style'=>'margin-bottom:5px']) ?>
    <?= Html::a(
       '<i class="fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.vendor.cancel_seven', ['ru'=>'Отмена']),'#',
       ['class' => 'btn btn-gray', 'data-dismiss'=>'modal',
        'id' => 'btn_cat_cancel',
        'style'=>'margin-bottom:5px;']) 
    ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>