<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SmsSendSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'СМС сообщения');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sms-send-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'created_at',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
                }
            ],
            'target',
            'text:ntext',
            'status.text',
            [
                'attribute' => 'updated_at',
                'value' => function ($data) {
                    if($data->created_at === $data->updated_at) {
                        return null;
                    } else {
                        return Yii::$app->formatter->asTime($data->updated_at, "php:j M Y, H:i:s");
                    }
                }
            ]
        ],
    ]); ?>
</div>