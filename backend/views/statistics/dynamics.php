<?php

use yii\widgets\Pjax;
use yii\grid\GridView;
use kartik\date\DatePicker;
use yii\widgets\ActiveForm;
use kartik\export\ExportMenu;

$this->title = implode(' - ', [
    Yii::t('app', 'Статистика'),
    Yii::t('app', 'Динамика использования системы в срезе по организациям')
]);

$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        $(document).on("change", "#startDate", function() {
            if (!justSubmitted) {
                $("#startDateForm").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
    });
        ');

$gridColumns = [
    'org_name',
    'org_id',
    'franchisee_name',
    'org_contact_name',
    'org_email',
    'org_city',
    'org_type',
    'org_registred',
    'order_max_date',
    'order_cnt',
    ['format'         => 'raw',
     'attribute'      => 'w5_count',
     'encodeLabel'    => false,
     'value'          => function ($data) {
         return $data['w5_sum'] . ' / ' . $data['w5_count'] . ' / ' . $data['w5_vendor'];
     },
     'contentOptions' => ['style' => 'width: 20%;'],
    ],
    ['format'         => 'raw',
     'attribute'      => 'w4_count',
     'encodeLabel'    => false,
     'value'          => function ($data) {
         return $data['w4_sum'] . ' / ' . $data['w4_count'] . ' / ' . $data['w4_vendor'];
     },
     'contentOptions' => ['style' => 'width: 20%;'],
    ],
    ['format'         => 'raw',
     'attribute'      => 'w3_count',
     'encodeLabel'    => false,
     'value'          => function ($data) {
         return $data['w3_sum'] . ' / ' . $data['w3_count'] . ' / ' . $data['w3_vendor'];
     },
     'contentOptions' => ['style' => 'width: 20%;'],
    ],
    ['format'         => 'raw',
     'attribute'      => 'w2_count',
     'encodeLabel'    => false,
     'value'          => function ($data) {
         return $data['w2_sum'] . ' / ' . $data['w2_count'] . ' / ' . $data['w2_vendor'];
     },
     'contentOptions' => ['style' => 'width: 20%;'],
    ],
    ['format'         => 'raw',
     'attribute'      => 'w1_count',
     'encodeLabel'    => false,
     'value'          => function ($data) {
         return $data['w1_sum'] . ' / ' . $data['w1_count'] . ' / ' . $data['w1_vendor'];
     },
     'contentOptions' => ['style' => 'width: 20%;'],
    ],

]

?>
<div class="row">
    <div class="col-md-12">

        <?php
        echo ExportMenu::widget([
            'dataProvider'    => $DataProvider,
            'columns'         => $gridColumns,
            'target'          => ExportMenu::TARGET_BLANK,
            'batchSize'       => 200,
            'timeout'         => 0,
            'exportConfig'    => [
                ExportMenu::FORMAT_HTML    => false,
                ExportMenu::FORMAT_TEXT    => false,
                ExportMenu::FORMAT_EXCEL   => false,
                ExportMenu::FORMAT_PDF     => false,
                ExportMenu::FORMAT_CSV     => false,
                ExportMenu::FORMAT_EXCEL_X => [
                    'label'       => Yii::t('kvexport', 'Excel 2007+ (xlsx)'),
                    'icon'        => 'floppy-remove',
                    'iconOptions' => ['class' => 'text-success'],
                    'linkOptions' => [],
                    'options'     => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
                    'alertMsg'    => Yii::t('kvexport', 'The EXCEL 2007+ (xlsx) export file will be generated for download.'),
                    'mime'        => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'extension'   => 'xlsx',
                    'writer'      => 'Xlsx'
                ],
            ],
        ]);
        ?>

        <?php
        Pjax::begin(['enablePushState' => true, 'id' => 'ReportList', 'timeout' => 30000]);
        $form = ActiveForm::begin([
            'options' => [
                'data-pjax' => false,
                'id'        => 'startDateForm',
            ],
            'method'  => 'get',
        ]);
        ?>
        <div class="col-md-12 text-center">
            <h3> Дата начала отчета</h3>
            <div class="form-group" style="width: 350px; margin: 0 auto; padding-bottom: 10px;">
                <?=
                DatePicker::widget([
                    'name'          => 'start_date',
                    'value'         => $start_date,
                    'options'       => ['placeholder' => 'Начальная Дата', 'id' => 'startDate'],
                    'pluginOptions' => [
                        'todayHighlight' => true,
                        'format'         => 'dd.mm.yyyy', //'d M yyyy',//
                        'autoclose'      => true,
                        'endDate'        => "0d",
                    ]
                ])
                ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <?=
        GridView::widget([
            'dataProvider' => $DataProvider,
            'filterModel'  => $SearchModel,
            'pager'        => [
                'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed
            ],
            'columns'      => $gridColumns,
        ]);
        ?>

        <?php Pjax::end() ?>
    </div>
    <div style="display: block; width: 2000px"><br></div>
</div>

