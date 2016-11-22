<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Organizations';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    'id',
    'type_id',
    [
        'format' => 'raw',
        'attribute' => 'name',
        'value' => function ($data) {
            return Html::a($data['name'], ['organization/view', 'id' => $data['id']]);
        },
    ],
    'city',
    'address',
    'zip_code',
    'phone',
    'email:email',
    'website',
                // 'created_at',
                // 'updated_at',
                // 'step',
        ];
        ?>
        <div class="organization-index">

            <h1><?= Html::encode($this->title) ?></h1>
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
            <?php
            echo ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'columns' => $gridColumns,
                'target' => ExportMenu::TARGET_SELF,
                'exportConfig' => [
                    ExportMenu::FORMAT_PDF => false,
                    ExportMenu::FORMAT_EXCEL_X => false,
                ],
            ]);
            ?>
            <?php Pjax::begin(['enablePushState' => false, 'id' => 'organizationList', 'timeout' => 5000]); ?>    <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
            ]);
            ?>
        <?php Pjax::end(); ?></div>
