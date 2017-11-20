<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\web\View;
$this->registerCss('.select2-container .select2-selection--single .select2-selection__rendered {margin-top: 0px;}');
?>
<?php 
    
    $form = ActiveForm::begin([
    'id' => 'import-form',
    'enableAjaxValidation' => false,
    'options' => ['enctype' => 'multipart/form-data'],
    'action' => Url::toRoute(['vendor/import','id'=>Yii::$app->request->get('id')])])
?>

<?php  // Заглушка до исправления функций загрузки каталога, надо было срочно выкладывать релиз (Roman, 20.11.2017)
$importModel->importType = 1;  ?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Импорт из файла</h4>
</div>
<div class="modal-body">

<?= $form->field($importModel, 'importType')->dropDownList([
        '1' =>'Добавить новые товары',
        '2' =>'Обновить цены',
        '3' =>'Добавить на MixMarket'
    ],['disabled' => 'disabled']); ?>
<?= $form->field($importModel, 'importFile',['template' => "{error}\n{label}\n{hint}\n{input}"])->fileInput()->label('Выберите .XLSX') ?>
</div>
<div class="modal-footer">
    <div class="row">
        <div class="col-md-8" style="border-right: 1px solid #ccc;">
    <?= Html::a(
       '<i class="fa fa-list-alt"></i> Шаблон - добавить новые позиции',
       Url::to('@web/upload/template.xlsx'),
       ['class' => 'btn btn-default', 'style'=>'display:block;margin-bottom:5px;text-align:left']) 
    ?>

    <?php  // Загрушка см выше от 20.11.2017, временно убраны шаблоны загрузки для неработающих методов
    /*

    <?= Html::a(
       '<i class="fa fa-list-alt"></i> Шаблон - обновить цены',
       Url::to('@web/upload/template_update.xlsx'),
       ['class' => 'btn btn-default', 'style'=>'display:block;margin-bottom:5px;margin-left:0px;text-align:left']) 
    ?>
    <?= Html::a(
       '<i class="fa fa-list-alt"></i> Шаблон - добавить на MixMarket',
       Url::to('@web/upload/template_market.xlsx'),
       ['class' => 'btn btn-default', 'style'=>'display:block;margin-left:0px;text-align:left']) 
    ?>

*/ ?>

        </div>
        <div class="col-md-4">
    <?= Html::submitButton('<i class="glyphicon glyphicon-import"></i> Импорт',
            ['class' => 'btn btn-success import','style'=>'display:block;width:100%;margin-bottom:5px']) ?>
    <?= Html::a(
       '<i class="fa fa-ban"></i> Отмена</a>','#',
       ['class' => 'btn btn-gray', 'data-dismiss'=>'modal',
        'style'=>'display:block;width:100%;margin-left:0px;']) 
    ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>