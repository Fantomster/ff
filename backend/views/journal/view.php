<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Journal */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Журнал', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="journal-view">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'service.denom',
            'operation.denom',
            'operation.comment',
            'user.profile.full_name',
            'organization.name',
            [
                'header' => 'Ответ системы',
                'attribute' => 'record.response',
                'value' => function ($data) {
                    if(!isset($data->record['response'])) {
                        $r = $data->response;
                    } else {
                        $r = print_r(json_decode($data->record['response']),1);
                    }
                    return $r;
                }
            ],
            'log_guide',
            'type',
            [
                'header' => 'Дата операции',
                'attribute' => 'record.response_at',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y  H:i:s");
                }
            ]
        ],
    ]) ?>

</div>
