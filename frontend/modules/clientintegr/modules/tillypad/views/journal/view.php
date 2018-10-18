<?php

use yii\helpers\Html;
use yii\grid\GridView;

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
        <i class="fa fa-upload"></i> Журнал Tillypad
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграции',
                'url'   => ['/clientintegr/default'],
            ],
            $this->title,
        ],
    ])
    ?>
</section>

<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
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
                        'model'      => $model,
                        'attributes' => [
                            'id',
                            'service.denom',
                            'operation.denom',
                            'operation.comment',
                            'user.profile.full_name',
                            'organization.name',
                            [
                                'header'    => 'Запрос',
                                'attribute' => 'record.request',
                                'value'     => function ($data) {
                                    return print_r(json_decode($data->record['request']), 1);
                                }
                            ],
                            [
                                'attribute' => 'record.response',
                                'value'     => function ($data) {
                                    return print_r(json_decode($data->record['response']), 1);
                                }
                            ],
                            'log_guide',
                            'type',
                            [
                                'attribute' => 'record.request_at',
                                'value'     => function ($data) {
                                    return $data->record['request_at'];
                                }
                            ],
                            [
                                'header'    => 'Дата операции',
                                'attribute' => 'record.response_at',
                                'value'     => function ($data) {
                                    return $data->record['response_at'];
                                }
                            ]
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
