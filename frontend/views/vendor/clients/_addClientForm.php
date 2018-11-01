<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            'id'                   => 'add-client-form',
            'enableAjaxValidation' => false,
            'action'               => Url::toRoute(['vendor/ajax-add-client']),
            'options'              => [
                'class' => 'client-form',
            ],
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.vendor.invite_client_two', ['ru' => 'Отправить приглашение']) ?></h4>
</div>
<div class="modal-body">
    <?= $form->field($user, 'email') ?>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-gray" data-dismiss="modal"><?= Yii::t('message', 'frontend.views.vendor.cancel_twelwe', ['ru' => 'Отмена']) ?></a>
    <?= Html::button(Yii::t('message', 'frontend.views.vendor.send_two', ['ru' => 'Отправить']), ['class' => 'btn btn-success adds-client']) ?>
</div>
<?php ActiveForm::end(); ?>
