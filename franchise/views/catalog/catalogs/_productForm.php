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
    <h4 class="modal-title"><?= Yii::t('app', 'Редактировать продукт') ?></h4>
</div>
<div class="modal-body">
    <?= $form->field($catalogGoods, 'price') ?>

    <?= $form->field($catalogGoods, 'discount') ?>

    <?= $form->field($catalogGoods, 'discount_percent') ?>

    <?= $form->field($catalogGoods, 'discount_fixed') ?>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> ' . Yii::t('app', 'Сохранить') . ' ', ['class' => 'btn btn-success edit']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> <?= Yii::t('app', 'Отмена') ?></a>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
$("#cataloggoods-discount").on("keyup paste put", function(e){
$("#cataloggoods-discount_percent,#cataloggoods-discount_fixed").val(0)
})
$("#cataloggoods-discount_percent").on("keyup paste put", function(e){
$("#cataloggoods-discount,#cataloggoods-discount_fixed").val(0)

})
$("#cataloggoods-discount_fixed").on("keyup paste put", function(e){
$("#cataloggoods-discount_percent,#cataloggoods-discount").val(0)
})
');
?>