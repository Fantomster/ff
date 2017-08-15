<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Регионы доставки - поставщик';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    'id',
    [
        'format' => 'raw',
        'attribute' => 'name',
        'value' => function ($data) {
            return $data['name'];
        },
    ],
    [
        'format' => 'raw',
        'attribute' => '',
        'value' => function ($data) {
            if(common\models\DeliveryRegions::find()->where(['supplier_id'=>$data->id])->exists()){
            return Html::a('<span class="btn btn-sm btn-success">Изменить</span>', ['regions', 'id' => $data->id]);    
            }
            return Html::a('<span class="btn btn-sm btn-warning">Добавить</span>', ['regions', 'id' => $data->id]);
        },
    ],
    'white_list'
        ];
        ?>
        <div class="organization-index">

            <h1><?= Html::encode($this->title) ?></h1>
            <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
            ]);
            ?>
            <?php Pjax::end(); ?></div>
