<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\PromoActionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Промо-акции';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="promo-action-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <p>
        <?= Html::a('Добавить промо-акцию', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'code',
            'title',
            [
                'attribute' => 'message',
                'format' => 'raw',
                'value' => function($data) {
                             return strlen($data->message)< 200 ? $data->message : substr($data->message, 0, strpos($data->message, ' ', 200)).'...';
                },
                'contentOptions' => ['style' => 'word-wrap: break-word;white-space: pre-wrap;'],
            ],
            'created_at',
            'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
