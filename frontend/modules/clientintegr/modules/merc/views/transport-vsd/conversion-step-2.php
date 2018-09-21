<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\jui\AutoComplete;
use kartik\widgets\DateTimePicker;
use frontend\modules\clientintegr\modules\merc\models\createStoreEntryForm;
use kartik\widgets\Select2;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoPconst */
/* @var $dicConst \api\common\models\iiko\iikoDicconst */
/* @var $form yii\bootstrap\ActiveForm */
$this->title = Yii::t('app', 'frontend.client.integration.store_entry.conversion', ['ru' => 'Переработка']);
?>
<?php $form = ActiveForm::begin(['id' => 'StockEntryForm']); ?>
<section class="content-header">
    <h1 class="margin-right-350">
        <i class="fa fa-list-alt"></i> <?= Yii::t('app', 'frontend.client.integration.store_entry.conversion', ['ru' => 'Переработка']) ?>
    </h1>
    <?=
    \yii\widgets\Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru' => 'Интеграция']),
                'url' => ['/clientintegr/default'],
            ],
            Yii::t('app', 'frontend.client.integration.store_entry.conversion', ['ru' => 'Переработка'])
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body">

            <div class="panel-body">
                <ul class="fk-prev-next pull-right">
                    <?= '<li class="fk-prev">' . Html::a(Yii::t('message', 'frontend.views.vendor.back', ['ru' => 'Назад']), ['conversion-step-1']) . '</li>' ?>
                </ul>
            </div>
            <div class="dict-agent-form">
                <h4>Информация о продукции: </h4>
                <?= $form->field($model, 'batchID')->textInput(['maxlength' => true]); ?>

                <?php
                $url = \yii\helpers\Url::to(['product-items']);
                $desc = '';//empty($model->city) ? '' : City::findOne($model->city)->description;

                echo $form->field($model, 'product_name')->widget(Select2::classname(), [
                    'initValueText' => $desc, // set the initial display text
                    'options' => ['placeholder' => 'Укажите название продукции  ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Поиск...'; }"),
                        ],
                        'ajax' => [
                            'url' => $url,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    ],
                ]);
                ?>

                <?= $form->field($model, 'volume')->textInput(['maxlength' => true]); ?>

                <?=
                $form->field($model, 'unit')
                    ->dropDownList(createStoreEntryForm::getUnitList(), ['prompt' => 'не указано'])
                    ->label('Единица измерения', ['class' => 'label', 'style' => 'color:#555'])
                ?>

                <?php $model->perishable = isset($model->perishable) ? $model->perishable : true; ?>
                <?= $form->field($model, 'perishable')
                    ->radioList($model->getPerishableList()) ?>

                <div class="form-group required">
                    <?php echo '<label class="control-label"><b>Дата выработки продукции</b></label>';
                    echo $form->field($productionDate, 'first_date')->widget(DateTimePicker::classname(), [
                        'options' => ['placeholder' => 'Начальная дата в интервале, либо единичная дата'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'dd.mm.yyyy hh:ii'
                        ]
                    ])->label(false);
                    echo $form->field($productionDate, 'second_date')->widget(DateTimePicker::classname(), [
                        'options' => ['placeholder' => 'Конечная дата в интервале'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'dd.mm.yyyy hh:ii'
                        ]
                    ])->label(false);
                    ?>
                </div>
                <div class="form-group required">
                    <?php echo '<label class="control-label"><b>Дата окончания срока годности продукции</b></label>';
                    echo $form->field($expiryDate, 'first_date')->widget(DateTimePicker::classname(), [
                        'options' => ['placeholder' => 'Начальная дата в интервале, либо единичная дата'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'dd.mm.yyyy hh:ii'
                        ]
                    ])->label(false);
                    echo $form->field($expiryDate, 'second_date')->widget(DateTimePicker::classname(), [
                        'options' => ['placeholder' => 'Конечная дата в интервале', 'id' => 'inputdate-first_date'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'dd.mm.yyyy hh:ii'
                        ]
                    ])->label(false);
                    ?>
                </div>
                <div class="form-group">
                    <?php echo Html::submitButton(Yii::t('message', 'frontend.views.layouts.client.integration.create', ['ru' => 'Создать']), ['class' => 'btn btn-success', 'id' => 'alSubmitVSDButton']) ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php ActiveForm::end(); ?>
<?php
$customJs = <<< JS
 $("document").ready(function(){
        $("#StockEntryForm").on("change", "#createstoreentryform-producttype", function() {
             var form = $("#StockEntryForm");
             if ($(this).val() == "")
                 {
                     $("#createstoreentryform-product").val("");
                     $("#createstoreentryform-subproduct").val("");
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
        $("#StockEntryForm").on("change", "#createstoreentryform-product", function() {
             var form = $("#StockEntryForm");
             if ($(this).val() == "")
                 {
                     $("#createstoreentryform-subproduct").val("");
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
        $("#StockEntryForm").on("change", "#createstoreentryform-subproduct", function() {
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
        $("#StockEntryForm").on("change", "#createstoreentryform-subproduct", function() {
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

$("document").ready(function() {
  $("#StockEntryForm").on("change", "#inputdate-first_date", function() {
    $("#alSubmitVSDButton").prop('disabled', false);
  })
})

JS;
$this->registerJs($customJs, $this::POS_READY);
?>


