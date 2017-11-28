<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

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
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.vendor.org_info_three', ['ru'=>'Информация об организации']) ?></h4>
</div>
<div class="modal-body">
    <?php if(empty($relation_supp_rest->cat_id)){?>
    <div class="alert alert-warning alert-dismissible" style="margin: -15px -15px 15px -15px;">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true" style="right: -16px;">×</button>
                <h4><i class="icon fa fa-warning"></i> <?= Yii::t('message', 'frontend.views.vendor.attention', ['ru'=>'Внимание!']) ?> </h4>
                <?= Yii::t('message', 'frontend.views.vendor.catalog_needs', ['ru'=>'Необходимо назначить каталог для данного клиента.']) ?>
    </div>
    <?php } ?>
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
            $form->field($relation_supp_rest, 'cat_id')->dropDownList($catalogs, ['prompt' => '']);
            ?>
        </div>
    </div>
    <?php if ($canManage) { ?>
    <div class="row">
        <div class="col-md-12">
            <?=
            Select2::widget([
                'data' => $vendor->getManagersList(),
                'name' => 'associatedManagers',
                'value' => array_keys($organization->getAssociatedManagersList($vendor->id)),
                'theme' => 'krajee',
                'hideSearch' => true,
                'options' => ['multiple' => true, 'placeholder' => Yii::t('message', 'frontend.views.vendor.choose_manager', ['ru'=>'Выберите менеджера'])],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]);
            ?>
        </div>
    </div>
    <?php } ?>
</div>
<div class="modal-footer">
<?= Html::button('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.save_seven', ['ru'=>'Сохранить']) . ' ', ['class' => 'btn btn-success save-form']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.vendor.close_four', ['ru'=>'Закрыть']) ?></a>
</div>
<?php ActiveForm::end(); ?>