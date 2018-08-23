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
use api\common\models\RkAccess;
use frontend\controllers\ClientController;

$this->title = 'Интеграция с iiko Office';

$url = Url::to(['/client/ajax-set-agent-attr-payment-delay']);

$dataColumns = [
    'id',
    'denom',
    'comment',
    'is_active',
    'created_at',
    'updated_at',
    'payment_delay',
    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions' => ['style' => 'width: 6%;'],
        'template' => '{update}',
        'visibleButtons' => [
            'update' => true,
        ],
        'buttons' => [
            'update' => function ($url, $data) {
                return '<i class="fa fa-pencil" id="iiko-agent-'.$data->id.'" style="cursor: pointer"'.
                    ' data-action="changeIikoAgentAttributes" data-id="'.
                    $data->id.'" aria-hidden="true"></i>';
            },
        ],
    ],
];

$paymentDelayColumnNumber = array_search('payment_delay', $dataColumns);
if (!$paymentDelayColumnNumber) {$paymentDelayColumnNumber = -1000;}
$max = ClientController::MAX_DELAY_PAYMENT;

$js = <<< JS

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
                        json['error'] = "Во время работы скрипта произошла ошибка! Пожалуйста к администратору";
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
                        html: "Во время работы скрипта произошла ошибка! Пожалуйста к администратору",
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
        <i class="fa fa-upload"></i> <?=$this->title?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr'],
            ],
            $this->title
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    Контрагенты iikoOffice
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">
                        <?php

                        echo GridView::widget([
                            'dataProvider' => $dataProvider,
                            'pjax' => false,
                            'columns' => $dataColumns,
                            'filterPosition' => false,
                            'options' => ['class' => 'table-responsive'],
                            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'bordered' => false,
                            'striped' => true,
                            'condensed' => false,
                            'responsive' => false,
                            'hover' => true,
                            'resizableColumns' => false,
                            'export' => [
                                'fontAwesome' => true,
                            ],
                        ]);
                        ?>
                        <?= Html::a('Вернуться', ['/clientintegr/iiko/default'], ['class' => 'btn btn-success btn-export'])?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
