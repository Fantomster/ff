<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoPconst */
/* @var $dicConst \api\common\models\iiko\iikoDicconst */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">
    <?php $form = ActiveForm::begin(['id' => 'StockEntryForm']); ?>
    <?php echo $form->errorSummary($model); ?>
    <h4>Информация о продукции: </h4>
    <?= $form->field($model, 'batchID')->textInput(['maxlength' => true]); ?>

    <?=
    $form->field($model, 'productType')
        ->dropDownList(\api\common\models\merc\MercVsd::$product_types,['prompt' => 'не указано']);
    ?>

    <?=
    $form->field($model, 'product')
        ->dropDownList($model->getProductList(),['prompt' => 'не указано'])
    ?>

    <?=
    $form->field($model, 'subProduct')
        ->dropDownList($model->getSubProductList(),['prompt' => 'не указано'])
    ?>

    <?= $form->field($model, 'product_name')->textInput(['maxlength' => true]); ?>

    <?= $form->field($model, 'volume')->textInput(['maxlength' => true]); ?>

    <?=
    $form->field($model, 'unit')
        ->dropDownList(\frontend\modules\clientintegr\modules\merc\models\createStoreEntryForm::getUnitList(),['prompt' => 'не указано'])
        ->label(Yii::t('message', 'frontend.client.integration.recipient', ['ru' => 'Фирма-отравитель']), ['class' => 'label', 'style' => 'color:#555'])
    ?>
    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('message', 'frontend.views.layouts.client.integration.create', ['ru' => 'Создать']), ['class' =>'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
$customJs = <<< JS
 $("document").ready(function(){
        $("#StockEntryForm").on("change", "#createstoreentryform-producttype", function() {
             var form = $("#StockEntryForm");
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
        $("#StockEntryForm").on("change", "#createstoreentryform-product", function() {
             var form = $("#StockEntryForm");
            $.post(
                form.attr("action"),
                    form.serialize()
                    )
                    .done(function(result) {
                        form.replaceWith(result);
                    });
     }); 
 });        
 
  
 
 /*$(document).on("click", ".clear_filters", function () {
           $('#product_name').val(''); 
           $('#statusFilter').val(''); 
           $('#typeFilter').val('1');
           $('#dateFrom').val('');
           $('#dateTo').val('');
           $('#recipientFilter').val('');
           $("#search_form").submit();
    });*/
JS;
$this->registerJs($customJs, $this::POS_READY);
?>


