<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\web\View;
use kartik\select2\Select2;

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
    <h4 class="modal-title">Импорт продуктов</h4>
</div>
<div class="modal-body">
<?= $form->field($importModel, 'importFile')->fileInput() ?>
<?='<label class="control-label" for="importUnique">Уникальное поле</label>' ?>
<?=Select2::widget([
    'name' => 'importUnique',
    'value' => 'product',
    'data' => ['product'=>'Продукт','article'=>'Артикул'],
    'options' => ['multiple' => false],
    'hideSearch'=>true
]) ?>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-default" data-dismiss="modal">Отмена</a>
    <?= Html::submitButton('Импорт',['class' => 'btn btn-success import']) ?>
</div>
<?php ActiveForm::end(); ?>