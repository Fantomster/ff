<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\JournalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Журнал';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="journal-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php yii\widgets\Pjax::begin(['id' => 'table_journal', 'timeout' => 10000]) ?>
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions'=>function($model){
            if($model->type == 'error'){
                return ['class' => 'danger'];
            }
        },
        'columns' => [
            'id',
            [
                    'header' => 'Сервис',
                    'attribute' => 'service.denom'
            ],
            'operation.denom',
            'operation.comment',
            'user.profile.full_name',
            'organization.name',
            //'response:ntext',
            //'log_guide',
            'type',
            [
                'header' => 'Дата операции',
                'value' => function ($data) {
                    return $data->record['response_at'] ?? $data->record['created_at'];
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}'
            ],
        ],
    ]); ?>
    <?php yii\widgets\Pjax::end(); ?>
</div>