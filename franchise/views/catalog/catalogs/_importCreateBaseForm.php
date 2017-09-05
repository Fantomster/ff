<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\web\View;
?>
<?php $form = ActiveForm::begin([
    'id' => 'import-form',
    'enableAjaxValidation' => false,
    'options' => ['enctype' => 'multipart/form-data'],
    'action' => Url::toRoute(['vendor/import-base-catalog-from-xls'])])
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">ИМПОРТ ГЛАВНОГО КАТАЛОГА</h4>
</div>
<div class="modal-body">
<?= $form->field($importModel, 'importFile',['template' => "{error}\n{label}\n{hint}\n{input}"])->fileInput()->label('Выберите .XLSX') ?>
</div>
<div class="modal-footer">
    <?= Html::a(
       '<i class="fa fa-list-alt"></i> Скачать шаблон (XLS)',
       Url::to('@web/upload/template.xlsx'),
       ['class' => 'btn btn-default btn-sm pull-left','style' => ['margin-right'=>'10px;']]
   ) ?> 
    <?= Html::submitButton('<i class="glyphicon glyphicon-import"></i> Импорт',['class' => 'btn btn-success import']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="fa fa-ban"></i> Отмена</a>
</div>
<?php ActiveForm::end(); ?>