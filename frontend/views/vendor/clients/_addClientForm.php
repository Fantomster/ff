<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'add-client-form',
            'enableAjaxValidation' => false,
            'action' => Url::toRoute(['vendor/ajax-add-client']),
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Отправить приглашение</h4>
</div>
<div class="modal-body">
    <?= $form->field($user, 'email') ?>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-gray" data-dismiss="modal">Отмена</a>
    <?= Html::button('Отправить', ['class' => 'btn btn-success adds-client']) ?>
</div>
<?php ActiveForm::end(); ?>
