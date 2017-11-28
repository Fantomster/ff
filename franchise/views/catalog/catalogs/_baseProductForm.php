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
    <h4 class="modal-title"><?= $catalogBaseGoods->isNewRecord? Yii::t('app', 'franchise.views.catalog.catalogs.new_good', ['ru'=>'Новый товар']) : Yii::t('app', 'Корректировка товара') ?></h4>
</div>
<div class="modal-body">
    <?= $form->field($catalogBaseGoods, 'article') ?>

    <?= $form->field($catalogBaseGoods, 'product') ?>

    <?= $form->field($catalogBaseGoods, 'units') ?>

    <?= $form->field($catalogBaseGoods, 'price') ?>
    
    <?= $form->field($catalogBaseGoods, 'ed') ?>
    
    <?= $form->field($catalogBaseGoods, 'category_id')->dropDownList(common\models\Category::allCategory(),['prompt' => '']) ?>
    
    <?= $form->field($catalogBaseGoods, 'note')->textarea(['rows' => 3])->label(Yii::t('app', 'franchise.views.catalog.catalogs.good_comment', ['ru'=>'Комментарий к товару'])) ?>
    
    <?= $catalogBaseGoods->isNewRecord? $form->field($catalogBaseGoods, 'cat_id')->hiddenInput(['value'=> Yii::$app->request->get('id')])->label(false):'' ?>
</div>
<div class="modal-footer">
    <?= Html::button($catalogBaseGoods->isNewRecord ? '<i class="icon fa fa-plus-circle"></i> ' . Yii::t('app', 'franchise.views.catalog.catalogs.create', ['ru'=>'Создать']) . ' ' : '<i class="icon fa fa-save"></i> ' . Yii::t('app', 'franchise.views.catalog.catalogs.save', ['ru'=>'Сохранить']) . ' ', ['class' => $catalogBaseGoods->isNewRecord ? 'btn btn-success edit' : 'btn btn-success edit']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> <?= Yii::t('app', 'franchise.views.catalog.catalogs.cancel', ['ru'=>'Отмена']) ?></a>
</div>
<?php ActiveForm::end(); ?>
