<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use api\common\models\RkServicedata;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Доступы R-keeper White Server';

$this->params['breadcrumbs'][] = [
    'label' => 'Управление лицензиями',
    'url'   => '/integration'
];

$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    'id',
    'code',
    'name',
    [
        'attribute' => 'address',
        'label'     => 'Адрес',
    ],
    [
        'attribute'           => 'td',
        'filterType'          => \kartik\grid\GridView::FILTER_DATE,
        'filterWidgetOptions' => ([
            'model'         => $searchModel,
            'attribute'     => 'date',
            'pluginOptions' => [
                'autoclose' => true,
                'format'    => 'dd.mm.yyyy',
            ]
        ]),
        'value'               => function ($data) {
            return date('d.m.Y', strtotime($data->td));
        }
    ],
    [
        'attribute'           => 'last_active',
        'label'               => 'Посл. Активность',
        'filterType'          => \kartik\grid\GridView::FILTER_DATE,
        'filterWidgetOptions' => ([
            'model'         => $searchModel,
            'attribute'     => 'date',
            'pluginOptions' => [
                'autoclose' => true,
                'format'    => 'dd.mm.yyyy',
            ]
        ]),
        'value'               => function ($data) {
            return date('d.m.Y', strtotime($data->last_active));
        }
    ],
    [
        'attribute' => 'status_id',
        'filter'    => [0 => 'Не активно', 1 => 'Активно'],
        'value'     => function ($model) {
            if ($model) return ($model->status_id == 1) ? 'Активно' : 'Не активно';

        },
    ],
    [
        'class'         => 'kartik\grid\ExpandRowColumn',
        'width'         => '50px',
        'value'         => function ($model, $key, $index, $column) {
            return GridView::ROW_COLLAPSED;
        },
        'detail'        => function ($model, $key, $index, $column) {
            $wmodel = RkServicedata::find()->andWhere('service_id = :service_id', [':service_id' => $model->id])->one();

            if ($wmodel) {
                $wmodel = RkServicedata::find()->andWhere('service_id = :service_id', [':service_id' => $model->id]);
            } else {
                $wmodel = null;
            }
            $service_id = $model->id;

            return Yii::$app->controller->renderPartial('_expand-row-details', ['model' => $wmodel, 'service_id' => $service_id]);
        },
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'expandOneOnly' => true,
    ],
];
?>

<div class="organization-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

    <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
    <div class="catalog-index">
        <div class="box-header with-border">
            <div class="box-title pull-left">
                <?php print "<p>Последнее обновление списка лицензий UCS: <strong>" . $data_last_license . "</strong></p>"; ?>
                <?= Html::a('<i class="fa fa-sign-in"></i> Обновить доступы', ['getws'], ['class' => 'btn btn-md fk-button']) ?>
            </div>
        </div>
    </div>
    <div class="catalog-index">
        <div class="box-header with-border">
            <div class="box-title pull-right">
                <?php
                echo ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns'      => $gridColumns,
                    'target'       => ExportMenu::TARGET_SELF,
                    'batchSize'    => 200,
                    'timeout'      => 0,
                    'exportConfig' => [
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
            </div>
        </div>
    </div>
    <div class="catalog-index">
        <div class="box-header with-border">
            <div class="box-title pull-left">
                <?php
                $dataProvider->pagination->pageParam = 'page_outer';
                echo
                GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel'  => $searchModel,
                    'columns'      => $gridColumns,
                ]);
                ?>
            </div>
        </div>
    </div>
    <?php Pjax::end(); ?></div>
