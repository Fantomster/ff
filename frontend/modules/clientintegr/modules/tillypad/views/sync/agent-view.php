<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
use kartik\grid\GridView;
use kartik\editable\Editable;
use frontend\controllers\ClientController;
use \yii\web\JsExpression;

$this->title = 'Интеграция с Tillypad';

$url = Url::to(['/client/ajax-set-agent-attr-payment-delay']);

$dataColumns = [
    'id',
    'denom',
    [
        'class'           => 'kartik\grid\EditableColumn',
        'attribute'       => 'vendor_id',
        'label'           => 'Поставщик MixCart',
        'vAlign'          => 'middle',
        'width'           => '210px',
        'refreshGrid'     => true,
        'editableOptions' => function ($model) {
            return [
                'asPopover'    => true,
                'name'         => 'vendor_id',
                'formOptions'  => ['action' => ['agent-mapping']],
                'header'       => 'Поставщик MixCart',
                'size'         => 'md',
                'inputType'    => \kartik\editable\Editable::INPUT_SELECT2,
                'displayValue' => isset($model->vendor) ? $model->vendor->name : null,
                'options'      => [
                    'data'          => [$model->vendor_id => isset($model->vendor) ? $model->vendor->name : ''],
                    'options'       => ['placeholder' => 'Выберите поставщика из списка',
                    ],
                    'pluginOptions' => [
                        'minimumInputLength' => 2,
                        'ajax'               => [
                            'url'      => Url::toRoute(['agent-autocomplete']),
                            'dataType' => 'json',
                            'data'     => new JsExpression('function(params) { return {term:params.term}; }')
                        ],
                        'allowClear'         => true
                    ],
                ]
            ];
        }],
    'comment',
    'is_active',
    'created_at',
    'updated_at',
    'payment_delay',
    [
        'class'          => 'yii\grid\ActionColumn',
        'contentOptions' => ['style' => 'width: 6%;'],
        'template'       => '{update}',
        'visibleButtons' => [
            'update' => true,
        ],
        'buttons'        => [
            'update' => function ($url, $data) {
                return '<i class="fa fa-pencil" id="iiko-agent-' . $data->id . '" style="cursor: pointer"' .
                    ' data-action="changeIikoAgentAttributes" data-id="' .
                    $data->id . '" aria-hidden="true"></i>';
            },
        ],
    ],
];

$paymentDelayColumnNumber = array_search('payment_delay', $dataColumns);
if (!$paymentDelayColumnNumber) {
    $paymentDelayColumnNumber = -1000;
}
$max = ClientController::MAX_DELAY_PAYMENT;

$js = <<< JS

function check(id) {

    return isNum;
}
   
$('i').filter('[data-action="changeIikoAgentAttributes"]').on('click', function () {
    var title = "Отсрочка платежа<br />(не более $max дней)";
    var agentId = $(this).attr('data-id');
    var x = $paymentDelayColumnNumber;
    var delayPaymentDays = $('tr[data-key="' + agentId + '"] td[data-col-seq="' + x + '"]').html();
    swal({
        title: title,
        showCancelButton: true,
        html: "<input type=text id=swal-input value=" + delayPaymentDays + " class=swal2-input>",
        confirmButtonText: "Сохранить",
        cancelButtonText: "Отмена",
        preConfirm: function() {
            var s = '1234567890';
            var err = false;
            var value = $('#swal-input').val();
            for (var j = 0; j < value.length; j++) {
                var char = value.substring(j, j+1);
                if (s.indexOf(char) == -1) {
                    err = true;
                }
            }
            if (err == true) {
               swal.showValidationError('Необходимо ввести целое число!')
            } else if (value > $max) {
               swal.showValidationError('Отсрочка платежа не может превышать $max дней!')
            }
        }
    }).then(function (result) {
        if (result.dismiss === "cancel") {
            swal.close();
        } else {
            var val = $("#swal-input").val();
            var url = '$url';
            $.ajax({
                url: url,
                "data": {"delay_days": val, "agent_id": agentId},
                "type": "POST",
                "cache": false,
                "success": function (result) {
                    var json = result;
                    if (json['error'] == undefined) {
                        json['error'] = "Во время работы скрипта произошла ошибка! Пожалуйста, обратитесь к администратору";
                    }
                    if (json['status'] == 'Y') {
                        $('tr[data-key="' + agentId + '"] td[data-col-seq="' + x + '"]').html(val);
                    } else {
                        swal({
                        title: 'Ошибка!',
                            showCancelButton: false,
                            html: json['error'],
                            confirmButtonText: "Закрыть",
                        })
                   }
                },
                "error": function (result) {
                    swal({
                        title: 'Ошибка!',
                        showCancelButton: false,
                        html: "Во время работы скрипта произошла ошибка! Пожалуйста, обратитесь к администратору",
                        confirmButtonText: "Закрыть",
                    })
                }
            });
        }
    });
});
JS;
$this->registerJs($js);

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> <?= $this->title ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/clientintegr'],
            ],
            $this->title
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    Контрагенты Tillypad
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">
                        <?php

                        echo GridView::widget([
                            'dataProvider'     => $dataProvider,
                            'pjax'             => false,
                            'columns'          => $dataColumns,
                            'filterPosition'   => false,
                            'options'          => ['class' => 'table-responsive'],
                            'tableOptions'     => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter'        => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'bordered'         => false,
                            'striped'          => true,
                            'condensed'        => false,
                            'responsive'       => false,
                            'hover'            => true,
                            'resizableColumns' => false,
                            'export'           => [
                                'fontAwesome' => true,
                            ],
                        ]);
                        ?>
                        <?= Html::a('Вернуться', ['/clientintegr/tillypad/default'], ['class' => 'btn btn-success btn-export']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
