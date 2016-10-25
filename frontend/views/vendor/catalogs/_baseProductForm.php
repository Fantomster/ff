<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'product-form',
            'enableAjaxValidation' => false,
            'action' => $catalogBaseGoods->isNewRecord? Url::toRoute('vendor/ajax-create-product') : Url::toRoute(['vendor/ajax-update-product', 'id' => $catalogBaseGoods->id]),
            'options' => [
                'class' => 'product-form',
            ],
            //'validationUrl' => Url::toRoute('vendor/ajax-validate-product'),
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= $catalogBaseGoods->isNewRecord? 'Новый товар' : 'Корректировка товара' ?></h4>
</div>
<div class="modal-body">
    <?= $form->field($catalogBaseGoods, 'article') ?>

    <?= $form->field($catalogBaseGoods, 'product') ?>

    <?= $form->field($catalogBaseGoods, 'units') ?>

    <?= $form->field($catalogBaseGoods, 'price') ?>

    <?= $form->field($catalogBaseGoods, 'category_id')->dropDownList(common\models\Category::allCategory(),['prompt' => '']) ?>
    
    <?= $form->field($catalogBaseGoods, 'note')->textarea(['rows' => 3])->label('Комментарий к товару') ?>
    
    <?= $catalogBaseGoods->isNewRecord? $form->field($catalogBaseGoods, 'cat_id')->hiddenInput(['value'=> Yii::$app->request->get('id')])->label(false):'' ?>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-default" data-dismiss="modal">Отмена</a>
    <?= Html::button($catalogBaseGoods->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $catalogBaseGoods->isNewRecord ? 'btn btn-success edit' : 'btn btn-success edit']) ?>
</div>
<?php ActiveForm::end(); ?>
