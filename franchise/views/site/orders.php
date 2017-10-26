<?php

$this->title = Yii::t('app', 'Заказы');

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;
use common\models\Order;
use kartik\export\ExportMenu;
use common\assets\CroppieAsset;
use yii\web\View;
use common\models\Role;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);
?>
    <section class="content-header">
        <h1>
            <i class="fa fa-home"></i> Заказы ваших ресторанов
            <small>Список заказов подключенных вами ресторанов и их статус</small>
        </h1>
    </section>
    <section class="content">

        <div class="box box-info order-history">
            <div class="box-body">
                <?php
                $form = ActiveForm::begin([
                    'options' => [
                        'id' => 'searchForm',
                        //'class' => "navbar-form",
                        'role' => 'search',
                    ],
                ]);
                ?>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-6">
                        <?= Html::label('Поиск', null, ['style' => 'color:#555']) ?>
                        <div class="input-group  pull-left">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                            <?= Html::input('text', 'OrderSearch[search]', $searchModel['searchString'], ['class' => 'form-control', 'placeholder' => 'Поиск', 'id' => 'search', 'style' => 'width:300px']) ?>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <?=
                        $form->field($searchModel, 'status')
                            ->dropDownList(['0' => 'Все', '1' => 'Новый', '2' => 'Отменен', '3' => 'Выполняется', '4' => 'Завершен'], ['id' => 'statusFilterID'])
                            ->label('Статус', ['style' => 'color:#555'])
                        ?>
                    </div>
                    <div class="col-lg-5 col-md-6 col-sm-6">
                        <?= Html::label('Начальная дата / Конечная дата', null, ['style' => 'color:#555']) ?>
                        <div class="form-group" style="width: 300px; height: 44px;">
                            <?=
                            DatePicker::widget([
                                'model' => $searchModel,
                                'attribute' => 'date_from',
                                'attribute2' => 'date_to',
                                'options' => ['placeholder' => 'Дата', 'id' => 'dateFrom'],
                                'options2' => ['placeholder' => 'Конечная дата', 'id' => 'dateTo'],
                                'separator' => '-',
                                'type' => DatePicker::TYPE_RANGE,
                                'pluginOptions' => [
                                    'format' => 'dd.mm.yyyy', //'d M yyyy',//
                                    'autoclose' => true,
                                    'endDate' => "0d",
                                ]
                            ])
                            ?>
                        </div>
                    </div>
                    <?php
                    ActiveForm::end();
                    ?>
                    <div class="pull-right" style="margin-top: 30px; margin-right: 10px;">
                        <?= ExportMenu::widget([
                            'dataProvider' => $dataProvider,
                            'columns' => $exportColumns,
                            'fontAwesome' => true,
                            'filename' => 'Заказы - ' . date('Y-m-d'),
                            'encoding' => 'UTF-8',
                            'target' => ExportMenu::TARGET_SELF,
                            'showConfirmAlert' => false,
                            'showColumnSelector' => false,
                            'dropdownOptions' => [
                                'label' => '<span class="text-label">Скачать список</span>',
                                'class' => ['btn btn-outline-default btn-sm'],
                                'style' => 'margin-right:10px;',
                            ],
                            'exportConfig' => [
                                ExportMenu::FORMAT_HTML => false,
                                ExportMenu::FORMAT_TEXT => false,
                                ExportMenu::FORMAT_EXCEL => false,
                                ExportMenu::FORMAT_PDF => false,
                                ExportMenu::FORMAT_CSV => false,
                                ExportMenu::FORMAT_EXCEL_X => [
                                    'label' => Yii::t('kvexport', 'Excel'),
                                    'icon' => 'file-excel-o',
                                    'iconOptions' => ['class' => 'text-success'],
                                    'linkOptions' => [],
                                    'options' => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
                                    'alertMsg' => Yii::t('kvexport', 'Файл EXCEL( XLSX ) будет генерироваться для загрузки'),
                                    'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'extension' => 'xlsx',
                                    'writer' => 'Excel2007',
                                    'styleOptions' => [
                                        'font' => [
                                            'bold' => true,
                                            'color' => [
                                                'argb' => 'FFFFFFFF',
                                            ],
                                        ],
                                        'fill' => [
                                            'type' => PHPExcel_Style_Fill::FILL_NONE,
                                            'startcolor' => [
                                                'argb' => 'FFFFFFFF',
                                            ],
                                            'endcolor' => [
                                                'argb' => 'FFFFFFFF',
                                            ],
                                        ],
                                    ]
                                ],
                            ],
                            'onRenderSheet' => function ($sheet, $grid) {
                                $i = 2;
                                while ($sheet->cellExists("B" . $i)) {
                                    $sheet->setCellValue("B" . $i, html_entity_decode($sheet->getCell("B" . $i)));
                                    $i++;
                                }
                                $j = 2;
                                while ($sheet->cellExists("C" . $j)) {
                                    $sheet->setCellValue("C" . $j, html_entity_decode($sheet->getCell("C" . $j)));
                                    $j++;
                                }
                            }
                        ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?=
                        GridView::widget([
                            'id' => 'orderHistory',
                            'dataProvider' => $dataProvider,
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                            'filterModel' => $searchModel,
                            'filterPosition' => false,
                            'pjax' => true,
                            'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                            'summary' => '',
                            'options' => ['class' => 'table-responsive'],
                            'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                            'columns' => [
                                [
                                    'attribute' => 'id',
                                    'value' => 'id',
                                    'label' => '№',
                                ],
                                [
                                    'attribute' => 'clientName',
                                    'value' => 'client.name',
                                    'label' => 'Ресторан',
                                ],
                                [
                                    'attribute' => 'vendorName',
                                    'value' => 'vendor.name',
                                    'label' => 'Поставщик',
                                ],
                                [
                                    'attribute' => 'clientManager',
                                    'value' => 'createdByProfile.full_name',
                                    'label' => 'Заказ создал',
                                ],
                                [
                                    'attribute' => 'acceptedByProfile.full_name',
                                    'value' => 'acceptedByProfile.full_name',
//                                    'value' => function($data) {
//                                        $arr = [];
//                                        foreach ($data->orderChat as $chat){
//                                            if(in_array($chat->user->role_id, [Role::ROLE_SUPPLIER_MANAGER, Role::ROLE_SUPPLIER_EMPLOYEE, Role::ROLE_ADMIN])){
//                                                $arr[$chat->user->profile->full_name]=$chat->user->profile->full_name;
//                                            }
//                                        }
//                                        if(isset($data->acceptedByProfile->full_name)){
//                                            $arr[$data->acceptedByProfile->full_name] = $data->acceptedByProfile->full_name;
//                                        }
//                                        $string = '';
//                                        foreach ($arr as $key => $value){
//                                            $string.=$value;
//                                            if($key!=end($arr)){
//                                                $string.=', ';
//                                            }
//                                        }
//                                        return $string;
//                                    },
                                    'label' => 'Заказ принял',
                                    'contentOptions'   =>   ['class' => 'small_cell_prinyal'],
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'total_price',
                                    'value' => function ($data) {
                                        return (float)$data['total_price'] . '<i class="fa fa-fw fa-rub"></i>';
                                    },
                                    'label' => 'Сумма',
                                    'contentOptions' => ['style' => 'vertical-align:middle;font-weight:bold'],
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'created_at',
                                    'value' => function ($data) {
                                        $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                        return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                    },
                                    'label' => 'Дата создания',
                                ],
                                [
                                    'attribute' => 'status',
                                    'label' => 'Статус',
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        $statusClass = "";
                                        switch ($data['status']) {
                                            case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                                            case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                                $statusClass = 'new';
                                                break;
                                            case Order::STATUS_PROCESSING:
                                                $statusClass = 'processing';
                                                break;
                                            case Order::STATUS_DONE:
                                                $statusClass = 'done';
                                                break;
                                            case Order::STATUS_REJECTED:
                                            case Order::STATUS_CANCELLED:
                                                $statusClass = 'cancelled';
                                                break;
                                        }
                                        return '<span class="status ' . $statusClass . '"><i class="fa fa-circle-thin"></i> ' . Order::statusText($data['status']) . '</span>';//fa fa-circle-thin
                                    },
                                ]
                            ],
                        ]);
                        ?>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <?php
        Modal::begin([
            'id' => 'vendorInfo',
        ]);
        ?>
        <?php Modal::end(); ?>
    </section>
<?php
$url = Url::to(['site/orders']);
$customJs = <<< JS
var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {searchString: $('#search').val(), status: $('#statusFilterID').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

$('#statusFilterID').on("change", function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {status: $('#statusFilterID').val(), searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

$('#dateFrom').on("change", function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {status: $('#statusFilterID').val(), searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

$('#dateTo').on("change", function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {status: $('#statusFilterID').val(), searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

JS;
$this->registerJs($customJs, View::POS_READY);
