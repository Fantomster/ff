<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

?>

<div class="production-act-defect-form">


    <?php $form = ActiveForm::begin(['id' => 'ProductForm']); ?>

    <?=
    $form->field($model, 'productType')
        ->dropDownList(\api\common\models\merc\MercVsd::$product_types,['prompt' => 'не указано']);
    ?>

    <?=
    $form->field($model, 'product_guid')
        ->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\vetis\VetisProductByType::findAll(['productType' => $model->productType, 'last' => true, 'active' => true]), 'guid', 'name'),
            ['prompt' => 'не указано'])
    ?>

    <?=
    $form->field($model, 'subproduct_guid')
        ->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\vetis\VetisSubproductByProduct::findAll(['productGuid' => $model->product_guid, 'last' => true, 'active' => true]),'guid', 'name'),
            ['prompt' => 'не указано'])
    ?>

    <?= $form->field($model, 'name')->textInput();?>

    <?= $form->field($model, 'code')->textInput();?>

    <?= $form->field($model, 'globalID')->textInput();?>

    <?= $form->field($model, 'correspondsToGost',['template' => '{label} {input}{error}'])->checkbox([], false);?>

    <div id="gost" style="display: none;">
    <?= $form->field($model, 'gost')->textInput();?>
    </div>

    <?=
    $form->field($model, 'packagingType_guid')
        ->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\vetis\VetisPackingType::find()->all(), 'guid', 'name'),
            ['prompt' => 'не указано'])
    ?>

    <?=
    $form->field($model, 'unit_guid')
        ->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\vetis\VetisUnit::findAll(['last' => true, 'active' => true]), 'guid', 'name'),
            ['prompt' => 'не указано'])
    ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$customJs = <<< JS
 $("document").ready(function(){
        $("#ProductForm").on("change", "#productform-producttype", function() {
             var form = $("#ProductForm");
             if ($(this).val() == "")
                 {
                     $("#productform-product_guid").val("");
                     $("#productform-subproduct_guid").val("");
                 }
            $.post(
                form.attr("action"),
                    form.serialize()
                    )
                    .done(function(result) {
                        form.replaceWith(result);
                    });
     }); 
 });    

$("document").ready(function(){
        $("#ProductForm").on("change", "#productform-product_guid", function() {
             var form = $("#ProductForm");
             if ($(this).val() == "")
                 {
                     $("#productform-subproduct_guid").val("");
                 }
            $.post(
                form.attr("action"),
                    form.serialize()
                    )
                    .done(function(result) {
                        form.replaceWith(result);
                    });
     }); 
 });

$("document").ready(function(){
        $("#ProductForm").on("change", "#productform-correspondstogost", function() {
            if ($(this).prop("checked")) {
                $("#gost").show();
            }
            else 
                {
                    $("#gost").hide();
                    $("#productform-gost").val('');
                }
     }); 
 });
JS;
$this->registerJs($customJs, $this::POS_READY);
?>
