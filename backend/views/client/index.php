<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    [
        'format' => 'raw',
        'attribute' => 'id',
        'value' => function ($data) {
            return Html::a($data['id'], ['client/view', 'id' => $data['id']]);
        },
        'label' => 'Id',
    ],
    [
        'format' => 'raw',
        'attribute' => 'full_name',
        'value' => function ($data) {
            return Html::a($data['profile']['full_name'], ['client/view', 'id' => $data['id']]);
        },
        'label' => 'Полное имя',
    ],
    [
        'attribute' => 'phone',
        'value' => 'profile.phone',
        'label' => 'Телефон',
    ],
    'status',
    'email',
    [
        'format' => 'raw',
        'attribute' => 'org_name',
        'value' => function ($data) {
            return Html::a($data['organization']['name'], ['organization/view', 'id' => $data['organization_id']]);
        },
        'label' => 'Название организации',
    ],
    [
        'attribute' => 'org_type_id',
        'value' => 'organization.type.name',
        'label' => 'Тип',
        'filter' => common\models\OrganizationType::getList(),
    ],
    [
        'attribute' => 'role',
        'value' => 'role.name',
        'label' => 'Роль',
    ],
    ['class' => 'yii\grid\ActionColumn'],

//            'created_at',
//            'logged_in_at',
];
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php
    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'target' => ExportMenu::TARGET_SELF,
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
        'batchSize' => 200,
        'timeout' => 0
    ]);
    ?>
    <?php Pjax::begin(['enablePushState' => true, 'id' => 'userList', 'timeout' => 5000]); ?>    
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end(); ?></div>

