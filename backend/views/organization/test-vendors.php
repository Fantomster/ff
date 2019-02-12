<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Общий список тестовых вендоров';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    [
        'attribute' => 'vendor_id',
        'format'    => 'raw',
        'value'     => function ($data) {
            return Html::a($data['vendor_id'], ['organization/update-test-vendor', 'id' => $data['id']]);
        },
    ],
    [
        'attribute' => 'guide_name'
    ],
    [
        'attribute' => 'is_active',
        'format'    => 'raw',
        'filter'    => [0 => 'Нет', 1 => 'Да'],
        'value'     => function ($data) {
            if (!empty($data->is_active)) {
                return '<span class="text-success">Да</span>';
            }
            return '';
        }
    ],
];
?>

<div class="row">
    <div class="col-md-6">
        <p>
            <?= Html::a('Создать тестового вендора', ['create-test-vendor'], ['class' => 'btn btn-success']) ?>
        </p>
    </div>
    <div class="col-md-6">
        <p>
            <?= Html::a('Запуск обновления данных', ['start-test-vendors-updating'], ['class' => 'btn btn-danger']) ?>
        </p>
    </div>
</div>

<div class="organization-index">

    <h1><?= Html::encode($this->title) ?> .</h1>

    <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => $gridColumns,
    ]);
    ?>
    <?php Pjax::end(); ?></div>
