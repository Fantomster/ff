<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SmsSendSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Переводы смс сообщений';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sms-send-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'message'
            ],
            [
                'header' => 'Шаблон',
                'format' => 'raw',
                'value' => function($data){
                    $message = '';
                    foreach($data->messages as $m) {
                        $message .= $m->language.': '.$m->translation.'<br>';
                    }
                    return $message;
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{edit}',
                'buttons' => [
                    'edit' => function ($url, $model, $key) {
                        $customurl = Yii::$app->getUrlManager()->createUrl(['sms/message-update','id'=>$model->id]);
                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-pencil"></span>', $customurl,
                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                    },
                ],
            ],
        ],
    ]); ?>
</div>