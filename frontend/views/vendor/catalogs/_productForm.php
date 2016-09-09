<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'product-form',
            'enableAjaxValidation' => false,
            'action' => Url::toRoute(['vendor/step-3-update-product', 'id' => $catalogGoods->id]),
            'options' => [
                'class' => 'product-form',
            ],
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Редактировать продукт</h4>
</div>
<div class="modal-body">
    <?= $form->field($catalogGoods, 'price') ?>

    <?= $form->field($catalogGoods, 'discount') ?>

    <?= $form->field($catalogGoods, 'discount_percent') ?>

    <?= $form->field($catalogGoods, 'discount_fixed') ?>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-primary" data-dismiss="modal">Отмена</a>
    <?= Html::button('Сохранить', ['class' => 'btn btn-primary edit']) ?>
</div>
<?php ActiveForm::end(); ?>
