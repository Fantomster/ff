<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Настройки сервисов';
$this->params['breadcrumbs'][] = [
    'url'   => '/organization/index',
    'label' => 'Организации'
];
$this->params['breadcrumbs'][] = [
    'url'   => '/organization/' . $organization->id,
    'label' => $organization->name
];
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    [
        'format'    => 'raw',
        'attribute' => 'denom',
        'label'     => 'Сервис',
    ],
    [
        'attribute' => 'vendor',
        'label'     => 'Провайдер',
    ],
    [
        'format' => 'raw',
        'value'  => function ($data) use ($organization) {
            if ($data['license']['is_active_license'] == 1) {
                return Html::a('Редактировать', [
                    'organization/update-integration-settings',
                    'service_id' => $data['id'],
                    'org_id' => $organization->id
                ]);
            } else {
                return '';
            }
        }
    ],
];
?>

<div class="organization-index">
    <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => $gridColumns
    ]);
    ?>
    <?php Pjax::end(); ?>
</div>