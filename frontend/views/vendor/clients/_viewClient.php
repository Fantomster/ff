<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'client-form',
            'enableAjaxValidation' => false,
            'action' => Url::toRoute(['vendor/view-client', 'id' => $client_id]),
            'options' => [
                'class' => 'client-form',
            ],
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Информация об организации</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <?=
                $form->field($organization, 'name')->textInput(['readonly' => true]);
            ?>
        </div>
        <div class="col-md-6">
            <?=
                $form->field($organization, 'city')->textInput(['readonly' => true]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=
                $form->field($organization, 'address')->textInput(['readonly' => true]);
            ?>
        </div>
        <div class="col-md-6">
            <?=
                $form->field($organization, 'zip_code')->textInput(['readonly' => true]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=
                $form->field($organization, 'phone')->textInput(['readonly' => true]);
            ?>
        </div>
        <div class="col-md-6">
            <?=
                $form->field($organization, 'email')->textInput(['readonly' => true]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=
                $form->field($organization, 'website')->textInput(['readonly' => true]);
            ?>
        </div>
        <div class="col-md-6">
            <?= 
            $form->field($relation_supp_rest, 'cat_id')->dropDownList($catalogs,['prompt' => '']); 
            ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success save-form']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> Закрыть</a>
</div>
<?php ActiveForm::end(); ?>