<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SmsSendSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'СМС сообщения');
$this->params['breadcrumbs'][] = $this->title;
$model = new \common\models\SmsSend();
?>
<div class="sms-send-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= \kartik\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'           => 'created_at',
                'filterType'          => \kartik\grid\GridView::FILTER_DATE,
                'filterWidgetOptions' => ([
                    'model'         => $model,
                    'attribute'     => 'date',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format'    => 'dd-mm-yyyy',
                    ]
                ]),
                'value'               => function ($data) {
                    return date('d.m.Y', strtotime($data->created_at));
                }
            ],
            'target',
            'text:ntext',
            'status.text',
            [
                'attribute' => 'updated_at',
                'value'     => function ($data) {
                    if ($data->created_at === $data->updated_at) {
                        return null;
                    } else {
                        return Yii::$app->formatter->asTime($data->updated_at, "php:j M Y, H:i:s");
                    }
                }
            ]
        ],
    ]); ?>
</div>