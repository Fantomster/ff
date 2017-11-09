<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\web\View;
$this->registerCss('.select2-container .select2-selection--single .select2-selection__rendered {margin-top: 0px;}');
?>
<?php $form = ActiveForm::begin([
    'id' => 'import-form',
    'enableAjaxValidation' => false,
    'options' => ['enctype' => 'multipart/form-data'],
    'action' => Url::toRoute(['vendor/import-to-xls','id'=>Yii::$app->request->get('id')])])
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('app', 'Импорт товаров') ?></h4>
</div>
<div class="modal-body">

<?php //= Html::error($importModel, 'importFile', ['class' => 'help-block']); ?>
<?= $form->field($importModel, 'importFile',['template' => "{error}\n{label}\n{hint}\n{input}"])->fileInput()->label(Yii::t('app', 'Выберите .XLSX')) ?>
</div>
<div class="modal-footer">
    <?= Html::a(
                   '<i class="fa fa-list-alt"></i> ' . Yii::t('app', 'Скачать шаблон (XLS)') . ' ',
                   Url::to('@web/upload/template.xlsx'),
                   ['class' => 'btn btn-default pull-left','style' => ['margin-right'=>'10px;']]
               ) ?> 
    <?= Html::submitButton('<i class="glyphicon glyphicon-import"></i> Импорт',['class' => 'btn btn-success import']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="fa fa-ban"></i> <?= Yii::t('app', 'Отмена') ?></a>
    
</div>
<?php ActiveForm::end(); ?>