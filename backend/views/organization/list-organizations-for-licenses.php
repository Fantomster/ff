<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список организаций для создания лицензий';
$this->params['breadcrumbs'][] = $this->title;

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
    'address',
    [
        'attribute' => '',
        'label' => 'Создать лицензии',
        'format' => 'raw',
        'filter' => common\models\Organization::getStatusList(),
        'value' => function ($data) {
            return Html::a('<span class="btn btn-sm btn-warning">Добавить лицензии</span>', ['organization/add-license', 'id' => $data->id]);
        },
    ],
];
?>
<div class="organization-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('licenses-added')): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            <?php echo Yii::$app->session->getFlash('licenses-added'); ?>
        </div>
    <?php endif; ?>

    <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end(); ?></div>
