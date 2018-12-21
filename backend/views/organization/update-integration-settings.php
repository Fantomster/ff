<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $service \common\models\AllService */
/* @var $settings array */

$this->title = 'Настройки сервиса ' . $service->denom;
$this->params['breadcrumbs'][] = [
    'url'   => '/organization/index',
    'label' => 'Организации'
];
$this->params['breadcrumbs'][] = [
    'url'   => '/organization/' . $organization->id,
    'label' => $organization->name
];
$this->params['breadcrumbs'][] = [
    'url'   => '/organization/integration-settings/' . $organization->id,
    'label' => 'Список сервисов'
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
                    'org_id'     => $organization->id
                ]);
            } else {
                return '';
            }
        }
    ],
];
?>

<div class="organization-index">
    <h3><?= $this->title ?></h3>
    <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
    <?php
    /** @var \common\models\IntegrationSetting $setting */
    foreach ($settings as $setting): ?>
        <div>
            <?= $setting->name ?>
        </div>
    <?php endforeach; ?>
    <?php Pjax::end(); ?>
</div>