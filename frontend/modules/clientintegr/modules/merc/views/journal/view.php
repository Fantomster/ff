<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\JournalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Запись лога №' . $model->id;
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
use yii\widgets\Breadcrumbs;

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Журнал ВЕТИС Меркурий
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграции',
                'url' => ['/clientintegr/default'],
            ],
            $this->title,
        ],
    ])
    ?>
</section>

<section class="content-header">
    <?= $this->title ?>
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?= \yii\widgets\DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'service.denom',
                            'operation.denom',
                            'operation.comment',
                            'user.profile.full_name',
                            'organization.name',
                            [
                                'attribute' => 'response',
                                'type' => 'raw',
                                'value' => function ($data) {
                                    return $data['response'];
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
            </div>
        </div>
    </div>
</section>
