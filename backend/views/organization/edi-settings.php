<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Общий список настроек Edi организации ' . $organization->name;
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    [
        'format' => 'raw',
        'attribute' => 'id',
        'value' => function ($data) {
            return Html::a($data['id'], ['organization/update-edi-settings', 'id' => $data['id']]);
        },
    ],
    [
        'attribute' => 'type_id',
        'value' => 'ediProvider.name',
        'label' => 'Провайдер',
    ],
    'gln_code',
    'provider_priority',
    [
        'format' => 'raw',
        'value' => function ($data) {
            return Html::a('Редактировать', ['organization/update-edi-settings', 'id' => $data['id']]);
        }
    ],
];
?>

<?= Html::a('Добавить настройки EDI', ['organization/create-edi-settings', 'id' => $organization->id], ['class' => 'btn btn-default', 'style' => 'margin-bottom: 10px;']) ?>

<div class="organization-index">

    <h1><?= Html::encode($this->title) ?> .</h1>


    <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end(); ?></div>
