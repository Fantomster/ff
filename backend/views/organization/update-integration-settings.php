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
        'format'    => 'raw',
        'attribute' => 'setting.name',
        'label'     => 'Название настройки',
    ],
    [
        'attribute' => 'setting.comment',
        'label'     => 'Комментарий',
    ],
    [
        'format'    => 'raw',
        'attribute' => 'value',
        'label'     => 'Название настройки',
        'value'     => function ($data) {

            switch ($data->setting->type) {
                case 'dropdown_list':
                    return Html::dropDownList('setting_' . $data->setting_id, $data->value, json_decode($data->setting->item_list), ['data' => ['id' => $data->id, 'org_id' => $data->org_id, 'setting_id' => $data->setting_id], 'class' => 'setting_input']);
                    break;
                case 'input_text':
                    return Html::input('text', 'setting_' . $data->setting_id, $data->value, ['data' => ['id' => $data->id, 'org_id' => $data->org_id, 'setting_id' => $data->setting_id], 'class' => 'setting_input']);
                    break;
                case 'password':
                    return Html::input('password', 'setting_' . $data->setting_id, $data->value, ['data' => ['id' => $data->id, 'org_id' => $data->org_id, 'setting_id' => $data->setting_id], 'class' => 'setting_input']);
                    break;
            }



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
    <button type="button" onclick="submit()" class="btn btn-success pull-right">Сохранить</button>
    <?php Pjax::end(); ?>
</div>

<script type="text/javascript">
	function submit() {
		let data = [];
		$('.setting_input').each(function () {
			let element = {};
			element.id = $(this).data('id');
			element.org_id = $(this).data('org_id');
			element.setting_id = $(this).data('setting_id');
			element.value = $(this).val();
			data.push(element);
		});
		$.ajax({
			type: "POST",
			url: '/organization/ajax-update-integration-settings',
			data: {settings: data},
			success: function (result) {
				if (result == 1) {
					location.reload();
				}
			}
		});
	}
</script>