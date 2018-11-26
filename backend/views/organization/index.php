<?php

use common\models\Organization;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Общий список организаций';
$this->params['breadcrumbs'][] = $this->title;
$url = \yii\helpers\Url::to(['organization/ajax-update-status']);
$urlVendorIsWork = \yii\helpers\Url::to(['organization/ajax-update-vendor-is-work']);

$gridColumns = [
    [
        'format' => 'raw',
        'attribute' => 'id',
        'value' => function ($data) {
            return Html::a($data['id'], ['organization/view', 'id' => $data['id']]);
        },
    ],
    [
        'attribute' => 'type_id',
        'value' => 'type.name',
        'label' => 'Тип',
        'filter' => common\models\OrganizationType::getList(),
    ],
    [
        'format' => 'raw',
        'attribute' => 'name',
        'value' => function ($data) {
            return Html::a($data['name'], ['organization/view', 'id' => $data['id']]);
        },
    ],
    'white_list',
    'partnership',
    'locality',
//    'address',
//    'zip_code',
//    'phone',
//    'email:email',
    [
        'attribute' => 'place_id',
        'label' => 'GEO',
        'format' => 'raw',
        'value' => function ($data) {
            if(empty($data->place_id)){
                return  Html::a('<span class="text-danger">Добавить адрес</span>', ['update', 'id' => $data->id]);
            }else{
                return  Html::a('<span class="text-success">Актуализирован</span>', ['update', 'id' => $data->id]);
            }
        }
    ],
//    'address',
    [
        'attribute' => 'place_id',
        'label' => 'У франшизы',
        'format' => 'raw',
        'value' => function ($data) {
            if(!empty($data->franchiseeAssociate)){
              return '<span class="text-success">Да</span>';
            }
            return '';
        }
    ],
    [
        'attribute' => 'blacklisted',
        'label' => 'Статус',
        'format' => 'raw',
        'filter' => common\models\Organization::getStatusList(),
        'value' => function ($model) use($url) {
            //return $data->getStatus();
            return \kartik\select2\Select2::widget([
                'model' => $model,
                'attribute' => 'blacklisted',
                'data' => common\models\Organization::getStatusList(),
                'hideSearch' => true,
                'options' => [
                    'id' => 'blacklisted_'.$model->id,
                    'name' => 'blacklisted_'.$model->id,
                    'class' => 'alBlacklistClass',
                    'allowClear' => true
                ],
            ]);
        }
    ],
    [
        'attribute' => 'vendor_is_work',
        'label' => 'Работает ли поставщик',
        'format' => 'raw',
        'filter' => [0 => 'Не работает', 1 => 'Работает'],
        'value' => function ($model) use($urlVendorIsWork) {
            if ($model->type_id == Organization::TYPE_SUPPLIER) {
                //return $data->getStatus();
                return \kartik\select2\Select2::widget([
                    'model'      => $model,
                    'attribute'  => 'vendor_is_work',
                    'data'       => [1 => 'Работает', 0 => 'Не работает'],
                    'hideSearch' => true,
                    'options'    => [
                        'id'         => 'vendor_is_work_' . $model->id,
                        'name'       => 'vendor_is_work_' . $model->id,
                        'class'      => 'alVendorIsWork',
                        'allowClear' => true
                    ],
                ]);
            }
            return '';
        }
    ],
//    'website',
    // 'created_at',
    // 'updated_at',
    // 'step',
];
?>
<div class="organization-index">

    <h1><?= Html::encode($this->title) ?> .</h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php
    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'target' => ExportMenu::TARGET_SELF,
        'batchSize' => 200,
        'timeout' => 0,
        'noExportColumns' => [
            6,
            7
        ],
        'exportConfig' => [
            ExportMenu::FORMAT_HTML => false,
            ExportMenu::FORMAT_TEXT => false,
            ExportMenu::FORMAT_EXCEL => false,
            ExportMenu::FORMAT_PDF => false,
            ExportMenu::FORMAT_CSV => false,
            ExportMenu::FORMAT_EXCEL_X => [
                'label' => Yii::t('kvexport', 'Excel 2007+ (xlsx)'),
                'icon' => 'floppy-remove',
                'iconOptions' => ['class' => 'text-success'],
                'linkOptions' => [],
                'options' => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
                'alertMsg' => Yii::t('kvexport', 'The EXCEL 2007+ (xlsx) export file will be generated for download.'),
                'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'extension' => 'xlsx',
                'writer' => 'Xlsx'
            ],
        ],
    ]);
    ?>

    <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end(); ?></div>
<?php
$customJs = <<< JS

$(document).on('change', '.alBlacklistClass', function(e) {
    var value = $(this).val();
    var id = $(this).prop('id');
    $.ajax({
        url: "$url",
        type: "POST",
        data: {'value' : value, 'id' : id},
        cache: false,
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
})

$(document).on('change', '.alVendorIsWork', function(e) {
    var value = $(this).val();
    var id = $(this).prop('id');
    $.ajax({
        url: "$urlVendorIsWork",
        type: "POST",
        data: {'value' : value, 'id' : id},
        cache: false,
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
})

JS;

$this->registerJs($customJs, \yii\web\View::POS_READY);
?>
