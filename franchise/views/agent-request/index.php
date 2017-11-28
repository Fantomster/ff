<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'franchise.views.agent-request.your_requests', ['ru'=>'Ваши заявки на регистрацию организаций']);
$this->params['breadcrumbs'][] = $this->title;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.agent-request.your_requests_two', ['ru'=>'Ваши заявки на регистрацию организаций']) ?>
    </h1>
</section>
<section class="content">
    <div class="row hidden-xs">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <?= Html::a(Yii::t('app', 'franchise.views.agent-request.create_request', ['ru'=>'Создать заявку']), ['create'], ['class' => 'btn btn-success']) ?>
                </div>
                <div class="box-body">
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'id',
//            'agent_id',
                            'target_email:email',
                            'comment',
                            //'is_processed',
                            'created_at',
                            'updated_at',
                            ['class' => 'yii\grid\ActionColumn'],
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>