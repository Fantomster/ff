<?php

use unclead\multipleinput\TabularInput;
use kartik\widgets\Select2;
use yii\web\JsExpression;
use yii\bootstrap\Html;

?>
<?php
$form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'validateOnChange' => false,
    'validateOnSubmit' => true,
    'validateOnBlur' => false,
    'options' => ['style' => "width: 100%;"]]);

echo TabularInput::widget([
    'models' => $list,
    'attributeOptions' => [
        'enableAjaxValidation' => false,
        'enableClientValidation' => false,
        'validateOnChange' => false,
        'validateOnSubmit' => true,
        'validateOnBlur' => false,
    ],
    'columns' => [
        [
            'name'  => 'dwhouse_id',
            'title' => 'Склад',
            'type'  => 'dropDownList',
            'defaultValue' => function ($data)
            {
                return $data->dwhouse_id;
            },
            'items' => $dwhouses,
            'options' => [
                'readOnly' => (count($dwhouses) == 1),
                //'style' => 'width: 150px;',
            ]
        ],
        [
            'name'  => 'id',
            'title' => 'ID',
            'type'  => \unclead\multipleinput\MultipleInputColumn::TYPE_HIDDEN_INPUT,
        ],
        [
            'name' => 'product_id',
            'title' => 'Ном ID',
            'value' => function ($data) {
                return $data->product_id;
            },
            'enableError' => true,
            'options' => [
                'readOnly' => true,
                //'style' => 'width: 150px;',
            ]
        ],
        [
            'name' => 'runame',
            'title' => 'Наименование',
            'type' => Select2::classname(),
            'enableError' => true,
            'options' => function ($data) {
                    return [
                        'initValueText' => (isset($data->product_id)) ? [$data->product_id => $data->product->runame] : "Выберите наименование",
                        'showToggleAll' => false,
                        'pluginEvents' => [
                            "select2:select" => "function() { 
                                    $(this).closest(\"tr\").find(\"td\").eq(0).find(\"input\").val($(this).val());
                                }
                                ",
                        ],
                        'pluginOptions' => [
                            'minimumInputLength' => 2,
                            'ajax' => [
                                'url' => \Yii::$app->urlManagerDictionary-> createAbsoluteUrl(["/autocomplete/find-product"]),
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {term:params.term}; }')
                            ],
                            'allowClear' => true
                        ],
                    ];
            }
        ],
        [
            'name' => 'amount',
            'title' => 'Кол-во',
            'enableError' => true,
        ],
    ]
]); ?>
    <div class="form-group">
        <?php echo Html::submitButton($invoiceModel->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $invoiceModel->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>