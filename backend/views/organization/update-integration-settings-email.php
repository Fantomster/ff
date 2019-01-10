<?php

use kartik\grid\GridView;
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
        'format' => 'raw',
        'label'  => 'Тип сервера',
        'value'  => function ($data) {
            return Html::checkbox('setting_' . $data->id, $data->is_active, ['data' => ['id' => $data->id, 'org_id' => $data->organization_id], 'class' => 'setting_input is_active']);
        }
    ],
    [
        'format' => 'raw',
        'label'  => 'Тип сервера',
        'value'  => function ($data) {
            return Html::dropDownList('setting_' . $data->id, $data->server_type, ['imap' => 'imap'], ['data' => ['id' => $data->id, 'org_id' => $data->organization_id], 'class' => 'setting_input server_type']);
        }
    ],
    [
        'format' => 'raw',
        'label'  => 'Адрес почтового сервера',
        'value'  => function ($data) {
            return Html::input('text', 'setting_' . $data->id, $data->server_host, ['data' => ['id' => $data->id, 'org_id' => $data->organization_id], 'class' => 'setting_input server_host']);
        }
    ],
    [
        'format' => 'raw',
        'label'  => 'Порт сервера',
        'value'  => function ($data) {
            return Html::dropDownList('setting_' . $data->id, $data->server_port, [143 => 143, 993 => 993], ['data' => ['id' => $data->id, 'org_id' => $data->organization_id], 'class' => 'setting_input server_port']);
        }
    ],
    [
        'format' => 'raw',
        'label'  => 'Использовать SSL',
        'value'  => function ($data) {
            return Html::checkbox('setting_' . $data->id, $data->server_ssl, ['data' => ['id' => $data->id, 'org_id' => $data->organization_id], 'class' => 'setting_input server_ssl']);
        }
    ],
    [
        'format' => 'raw',
        'label'  => 'User',
        'value'  => function ($data) {
            return Html::input('text', 'setting_' . $data->id, $data->user, ['data' => ['id' => $data->id, 'org_id' => $data->organization_id], 'class' => 'setting_input user']);
        }
    ],
    [
        'format'    => 'raw',
        'attribute' => 'password',
        'label'     => 'Пароль',
        'value'     => function ($data) {
            /** @var $data \common\models\IntegrationSettingFromEmail */
            return Html::input('password', 'setting_' . $data->id, $data->getCountCharsPassword(), ['data' => ['id' => $data->id, 'org_id' => $data->organization_id], 'class' => 'setting_input password']);
        },
    ],

];
?>
    <div class="organization-index">
        <h3><?= $this->title ?></h3>
        <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'columns'      => $gridColumns,
        ]);
        ?>
        <button type="button" class="btn btn-success pull-right">Сохранить</button>
        <?php Pjax::end(); ?>
    </div>
<?php
$url = Yii::$app->urlManager->createUrl('/organization/ajax-update-integration-settings-email');
$customJs = <<< JS
		$(document).on('click', '.btn-success', function () {
			let data = [];
			$('.kv-table-wrap tbody tr').each(function () {
				let element = {};
				element.id = $(this).find('.server_type').data('id');
				element.org_id = $(this).find('.server_type').data('org_id');
				element.server_type = $(this).find('.server_type').val();
				element.server_host = $(this).find('.server_host').val();
				element.server_port = $(this).find('.server_port').val();
				element.server_ssl = +$(this).find('.server_ssl').is(':checked');
				element.user = $(this).find('.user').val();
				element.password = $(this).find('.password').val();
				element.is_active = +$(this).find('.is_active').is(':checked');
				data.push(element);
			});
			$.ajax({
				type: "POST",
				url: '$url',
				data: {settings: data},
				success: function (result) {
					if (result == 1) {
						location.reload();
					}
				}
			});
		});
JS;
$this->registerJs($customJs, \yii\web\View::POS_READY);
