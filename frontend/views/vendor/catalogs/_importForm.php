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
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.vendor.file_import', ['ru'=>'Импорт из файла']) ?></h4>
</div>
<div class="modal-body">

<?= $form->field($importModel, 'importType')->dropDownList([
        '1' =>Yii::t('message', 'frontend.views.vendor.add_new_goods', ['ru'=>'Добавить новые товары']),
        '2' =>Yii::t('message', 'frontend.views.vendor.refresh_prices', ['ru'=>'Обновить цены']),
        '3' =>Yii::t('message', 'frontend.views.vendor.add_on_mix', ['ru'=>'Добавить на MixMarket'])
    ]); ?>
<?= $form->field($importModel, 'importFile',['template' => "{error}\n{label}\n{hint}\n{input}"])->fileInput()->label(Yii::t('message', 'frontend.views.vendor.choose_xls_two', ['ru'=>'Выберите .XLSX'])) ?>
</div>
<div class="modal-footer">
    <div class="row">
        <div class="col-md-8" style="border-right: 1px solid #ccc;">
    <?= Html::a(
       '<i class="fa fa-list-alt"></i> ' . Yii::t('message', 'frontend.views.vendor.templ_add_new_positions', ['ru'=>'Шаблон - добавить новые позиции']) . ' ',
       Url::to('@web/upload/template.xlsx'),
       ['class' => 'btn btn-default', 'style'=>'display:block;margin-bottom:5px;text-align:left']) 
    ?>
    <?= Html::a(
       '<i class="fa fa-list-alt"></i> ' . Yii::t('message', 'frontend.views.vendor.prices_upd', ['ru'=>'Шаблон - обновить цены']) . ' ',
       Url::to('@web/upload/template_update.xlsx'),
       ['class' => 'btn btn-default', 'style'=>'display:block;margin-bottom:5px;margin-left:0px;text-align:left']) 
    ?>
    <?= Html::a(
       '<i class="fa fa-list-alt"></i> ' . Yii::t('message', 'frontend.views.vendor.templ_add_on_mix', ['ru'=>'Шаблон - добавить на MixMarket']) . ' ',
       Url::to('@web/upload/template_market.xlsx'),
       ['class' => 'btn btn-default', 'style'=>'display:block;margin-left:0px;text-align:left']) 
    ?>
        </div>
        <div class="col-md-4">
    <?= Html::submitButton('<i class="glyphicon glyphicon-import"></i> ' . Yii::t('message', 'frontend.views.vendor.import_three', ['ru'=>'Импорт']) . ' ',
            ['class' => 'btn btn-success import','style'=>'display:block;width:100%;margin-bottom:5px']) ?>
    <?= Html::a(
       '<i class="fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.vendor.cancel_seven', ['ru'=>'Отмена']) . ' </a>','#',
       ['class' => 'btn btn-gray', 'data-dismiss'=>'modal',
        'style'=>'display:block;width:100%;margin-left:0px;']) 
    ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>