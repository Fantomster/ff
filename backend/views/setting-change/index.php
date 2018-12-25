<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IntegrationSettingChangeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список запросов об изменении настроек.';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="integration-setting-change-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'format'    => 'raw',
                'attribute' => 'org_name',
                'value'     => function ($data) {
                    return Html::a($data->organization->name, ['organization/view', 'id' => $data->organization->id]);
                },
            ],
            [
                'format'    => 'raw',
                'attribute' => 'setting_name',
                'value'     => function ($data) {
                    return $data->integrationSetting->name;
                },
            ],
            [
                'format'    => 'raw',
                'attribute' => 'setting_comment',
                'value'     => function ($data) {
                    return $data->integrationSetting->comment;
                },
            ],
            'old_value',
            'new_value',
            [
                'format'    => 'raw',
                'attribute' => 'created_at',
                'filter'    => false
            ],

            [
                'class'         => 'yii\grid\ActionColumn',
                'header'        => 'Действия',
                'headerOptions' => ['width' => '80'],
                'template'      => '{confirm}',
                'buttons'       => [
                    'confirm' => function ($url) {
                        return Html::a('<span class="btn btn-sm btn-success">Применить</span>', $url, [
                            'data' => [
                                'confirm' => 'Вы действительно хотите применить данную настройку? Отменить действие будет невозможно!',
                                'method'  => 'post',
                            ]
                        ]);
                    }
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
