<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Заявки на регистрацию организаций';
$this->params['breadcrumbs'][] = $this->title;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Заявки на регистрацию организаций
    </h1>
</section>
<section class="content">
    <div class="row hidden-xs">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body">
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            'id',
                            'target_email:email',
                            [
                                'attribute' => 'comment',
                                'label' => 'Комментарий'
                            ],
                            //'is_processed',
                            [
                                    'attribute' => 'full_user_name',
                                    'label' => 'ФИО агента'
                            ],
                            [
                                'attribute' => 'user_email',
                                'label' => 'Email агента'
                            ],
                            'created_at',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url,$model) {
                                        $customurl=Yii::$app->getUrlManager()->createUrl(['agent-request/view','id'=>$model['id']]);
                                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-eye-open"></span>', $customurl,
                                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                                    },
                                ],
                            ],
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>