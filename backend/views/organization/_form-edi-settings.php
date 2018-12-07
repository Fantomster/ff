<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Organization */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="organization-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'gln_code')->textInput(['maxlength' => true, 'required' => true]) ?>

    <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pass')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'int_user_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'token')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'provider_priority')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pricat_action_attribute_rule')->dropDownList(\api_web\components\Registry::$edi_pricat_statuses)->label('Тип обработки документа pricat') ?>

    <?php
    if ($model->isNewRecord):
        ?>

        <?= $form->field($model, 'provider_id')->dropDownList($providers)->label('Название провайдера') ?>

        <?php
    else:
        ?>

        <h2><?= $model->ediProvider->name ?></h2>

        <?php
    endif;
    ?>

    <h3>Выберите организации</h3>

    <div id="alEdiList">
        <?= $this->render('list-organizations-for-edi', [
            'ediOrganizations'     => $ediOrganizations,
            'checkedOrganizations' => $checkedOrganizations ?? null
        ]) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$url = \yii\helpers\Url::to(['organization/ajax-update-edi-list']);
$customJs = <<< JS

$(document).on('change', '#ediorganization-provider_id', function(e) {
    var value = $(this).val();
    $.ajax({
        url: "$url",
        type: "POST",
        data: {'value' : value, 'org_id' : "$orgID"},
        cache: false,
        success: function(res) {
          $('#alEdiList').html(res);
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
})

JS;

$this->registerJs($customJs, \yii\web\View::POS_READY);
?>
