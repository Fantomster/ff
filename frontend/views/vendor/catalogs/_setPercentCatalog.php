<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'set_discount_percent',
            'enableAjaxValidation' => false,
            'action' => Url::toRoute(['vendor/ajax-set-percent', 'id' => $cat_id]),
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Установить % на весь ассортимент</h4>
</div>
<div class="modal-body">
    <?=$form->field($catalogGoods, 'discount_percent')->textInput(['class' => 'form-control input-md']); ?>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-primary" data-dismiss="modal">Отмена</a>
    <?= Html::button('Сохранить', ['class' => 'btn btn-primary set']) ?>
</div>
<?php ActiveForm::end(); ?>
