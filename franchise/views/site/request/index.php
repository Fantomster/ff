<?php

$this->title = Yii::t('app', 'franchise.views.site.request.req', ['ru'=>'Заявки']);

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Modal;
use common\models\Order;
use kartik\export\ExportMenu;
use common\assets\CroppieAsset;
use yii\web\View;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);
?>

<section class="content-header">
    <h1>
        <i class="fa fa-home"></i>  <?= Yii::t('app', 'franchise.views.site.request.rest_req', ['ru'=>'Заявки ваших ресторанов']) ?>
        <small><?= Yii::t('app', 'franchise.views.site.request.rest_req_list', ['ru'=>'Список заявок подключенных вами ресторанов']) ?></small>
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
                    <?= Html::label(Yii::t('app', 'franchise.views.site.request.search', ['ru'=>'Поиск']), null, ['style' => 'color:#555']) ?>
                    <div class="input-group  pull-left">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                        <?= Html::input('text', 'search', $searchModel['searchString'], ['class' => 'form-control', 'placeholder' => Yii::t('app', 'franchise.views.site.request.search_two', ['ru'=>'Поиск']), 'id' => 'search', 'style' => 'width:300px']) ?>
                    </div>
                </div>
                <div class="col-lg-5 col-md-6 col-sm-6">
                        <?= Html::label(Yii::t('app', 'franchise.views.site.request.date_from_to', ['ru'=>'Начальная дата / Конечная дата']), null, ['style' => 'color:#555']) ?>
                    <div class="form-group" style="width: 300px; height: 44px;">
                        <?=
                        DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'date_from',
                            'attribute2' => 'date_to',
                            'options' => ['placeholder' => Yii::t('app', 'franchise.views.site.request.date_from', ['ru'=>'Дата']), 'id' => 'dateFrom'],
                            'options2' => ['placeholder' => Yii::t('app', 'franchise.views.site.request.date_to', ['ru'=>'Конечная дата']), 'id' => 'dateTo'],
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
                        'filename' => Yii::t('app', 'franchise.views.site.request.orders', ['ru'=>'Заявки- ']) . date('Y-m-d'),
                        'encoding' => 'UTF-8',
                        'target' => ExportMenu::TARGET_SELF,
                        'showConfirmAlert' => false,
                        'showColumnSelector' => false,
                        'batchSize' => 200,
                        'timeout' => 0,
                        'dropdownOptions' => [
                            'label' => '<span class="text-label">' . Yii::t('app', 'franchise.views.site.request.download_list', ['ru'=>'Скачать список']) . ' </span>',
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
                                //'writer' => 'Excel2007',
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
                            'product',
                            [
                                'attribute' => 'categoryName.name',
                                'label' => Yii::t('app', 'franchise.views.site.request.category', ['ru'=>'Категория']),
                                'value' => function ($data) {
                                    return Yii::t('app', $data['categoryName']['name']);
                                },
                            ],
                            'amount',
                            'comment',
                            [
                                'attribute' => 'client.name',
                                'label' => Yii::t('app', 'franchise.views.site.request.rest_name', ['ru'=>'Название ресторана']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function ($data) {
                                    $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                },
                                'label' => Yii::t('app', 'franchise.views.site.request.creating_date', ['ru'=>'Дата создания']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'active_status',
                                'value' => function ($data) {
                                    return ($data['active_status'])?Yii::t('app', 'franchise.views.site.request.opened', ['ru'=>'Открыта']):'<span style="color: red;">' . Yii::t('app', 'franchise.views.site.request.closed', ['ru'=>'Закрыта']) . ' </span>';
                                },
                                'label' => Yii::t('app', 'franchise.views.site.request.status', ['ru'=>'Статус']),
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} &nbsp; {edit}',
                                'buttons' => [
                                    'view' => function ($url,$model) {
                                        $customurl=Yii::$app->getUrlManager()->createUrl(['site/request','id'=>$model['id']]);
                                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-eye-open"></span>', $customurl,
                                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                                    },
                                    'edit' => function ($url,$model) {
                                        $customurl=Yii::$app->getUrlManager()->createUrl(['site/update-request','id'=>$model['id']]);
                                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-pencil"></span>', $customurl,
                                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                                    },
                                ],
                            ],
                        ],
                    ]);
                    ?>
                </div></div>
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
$url = Url::to(['site/requests']);
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

