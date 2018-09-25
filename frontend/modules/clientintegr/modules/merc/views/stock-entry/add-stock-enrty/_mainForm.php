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
?>

<div class="dict-agent-form">
    <?php $form = ActiveForm::begin(['id' => 'StockEntryForm']); ?>
    <?php echo $form->errorSummary([$model, $productionDate, $expiryDate, $inputDate]); ?>
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

    <?php $model->getProductName() ?>
    <?= $form->field($model, 'product_name')->widget(
    AutoComplete::className(), [
    'clientOptions' => [
    'source' => $model->getProductName(),
    ],
    'options'=>[
    'class'=>'form-control'
    ]
    ])
    ?>

    <?= $form->field($model, 'volume')->textInput(['maxlength' => true]); ?>

    <?=
    $form->field($model, 'unit')
        ->dropDownList(createStoreEntryForm::getUnitList(),['prompt' => 'не указано'])
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
            'options' => ['placeholder' => 'Конечная дата в интервале'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd.mm.yyyy hh:ii'
            ]
        ])->label(false);
        ?>
    </div>
    <h4>Сведения о происхождении продукции: </h4>
    <?php
    $model->country = isset($model->country) ? $model->country : '74a3cbb1-56fa-94f3-ab3f-e8db4940d96b';
    echo $form->field($model, 'country')
        ->dropDownList(createStoreEntryForm::getCountryList(),['prompt' => 'не указано',
           /* 'options'=>[
            '7' => ['label' => 'JULY', 'selected'=>true],
        ]*/]);
    ?>
    <?php
    $url = \yii\helpers\Url::to(['producers-list']);
    $desc = '';//empty($model->city) ? '' : City::findOne($model->city)->description;

    echo $form->field($model, 'producer')->widget(Select2::classname(), [
        'initValueText' => $desc, // set the initial display text
        'options' => ['placeholder' => 'Укажите название предприятия для поиска  ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Загрузка результатов...'; }"),
            ],
            'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term,c:$( "#createstoreentryform-country option:selected" ).val()}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            /*'templateResult' => new JsExpression('function(city) { return producer.text; }'),
            'templateSelection' => new JsExpression('function (city) { return producer.text; }'),*/
        ],
    ]);
    ?>

    <h4>Сведения о бумажном ВСД: </h4>
    <?= $form->field($model, 'vsd_issueSeries')->textInput(['maxlength' => true]); ?>
    <?= $form->field($model, 'vsd_issueNumber')->textInput(['maxlength' => true]); ?>
    <div class="form-group required">
        <?php echo '<label class="control-label"><b>Дата бумажного ВСД</b></label>';
        echo $form->field($inputDate, 'first_date')->widget(\kartik\widgets\DatePicker::classname(), [
            'options' => ['placeholder' => 'Дата бумажного ВСД'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd.mm.yyyy'
            ]
        ])->label(false);
        ?>
    </div>
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


