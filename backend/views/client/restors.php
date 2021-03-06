<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\widgets\Pjax;
use common\models\Job;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Сотрудники ресторанов';
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
        'attribute' => 'org_name',
        'value' => function ($data) {
            return Html::a($data['organization']['name'], ['organization/view', 'id' => $data['organization_id']]);
        },
        'label' => 'Название организации',
    ],
    [
        'format' => 'raw',
        'attribute' => 'job',
        'value' => function ($data) {
            if ($data['profile']['job_id']) {
                $jn = Job::find()
                    ->select(['name_job'])
                    ->where(['id' => $data['profile']['job_id']])
                    ->one();
                return $jn->name_job;
            } else {
                return 'Не указана';
            }
        },
        'label' => 'Должность сотрудника',
        'filter' => common\models\Job::getListRestor(),
    ],
    [
        'format' => 'raw',
        'attribute' => 'full_name',
//                'value' => 'profile.full_name',
        'value' => function ($data) {
            return Html::a($data['profile']['full_name'], ['client/view', 'id' => $data['id']]);
        },
        'label' => 'Полное имя',
    ],
    [
        'attribute' => 'gender',
        'value' => function ($data) {
            switch ($data['profile']['gender']) {
                case 1:
                    $gender = 'Мужской';
                    break;
                case 2:
                    $gender = 'Женский';
                    break;
                case null:
                    $gender = 'Не указан';
                    break;
            }
                return $gender;
        },

        'label' => 'Пол',
        'filter' => common\models\Gender::getList(),
    ],
    [
        'attribute' => 'phone',
        'value' => 'profile.phone',
        'label' => 'Телефон',
    ],
    [
        'attribute' => 'sms_subscribe',
        'value' => function ($data) {
            switch ($data['sms_subscribe']) {
                case 1:
                    $sms_allow = 'Согласен';
                    break;
                case 2:
                    $sms_allow = 'Не согласен';
                    break;
                case null:
                    $sms_allow = 'Не указано';
                    break;
            }
            return $sms_allow;
        },
        'label' => 'СМС-рассылки',
        'filter' => common\models\Allow::getList(),
    ],
    'email',
    [
        'attribute' => 'subscribe',
        'value' => function ($data) {
            switch ($data['subscribe']) {
                case 1:
                    $email_allow = 'Согласен';
                    break;
                case 2:
                    $email_allow = 'Не согласен';
                    break;
                case null:
                    $email_allow = 'Не указано';
                    break;
            }
            return $email_allow;
        },

        'label' => 'Email-рассылки',
        'filter' => common\models\Allow::getList(),
    ],
    [
        'attribute' => 'language',
        'value' => 'language',
        'label' => 'Язык',
        'filter' => \backend\models\UserSearch::getListToLanguage(),
    ],
    ['class' => 'yii\grid\ActionColumn'],
    [
        'attribute' => 'access_token',
        'value' => 'access_token',
        'label' => 'Токен',
        'contentOptions'=>['style'=>'white-space: normal; max-width: 5px; overflow:hidden; text-overflow:clip;']
    ],
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
