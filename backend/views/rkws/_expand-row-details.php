 <?php
 
 use kartik\grid\GridView;
 use yii\data\ActiveDataProvider;
 use common\models\User;
 use yii\helpers\Html;

 ?>
Доступы:<br><br>

<?php 
    
    if(empty($model)) {
        
         echo Html::a('Создать доступ', ['create','service_id'=>$service_id], ['class'=>'btn btn-md fk-button']);
    } else {
        echo Html::a('Создать доступ', ['create','service_id'=>$service_id], ['class'=>'btn btn-md fk-button']);

?>

        <?=
        GridView::widget([
            'dataProvider' => new ActiveDataProvider([
                'query' => $model,
                'pagination' => ['pageParam' => 'page_inner'],
                'sort' => false,
            ]),
            'layout' => '{items}{pager}',
            'pjax' => true, // pjax is set to always true for this demo
            'filterPosition' => false,
            'columns' => [
                'id',
                'service_id',
                'org',
                [
                    'attribute' => 'org',
                    'label' => 'Организация MixCart',
                    'value' => function ($model) {
                        if (isset($model))
                            return $model->organization ? $model->organization->name : null;

                    },

                ],
                'fd',
                'td',
                [
                    'attribute' => 'status_id',
                    'value' => function ($model) {
                        if ($model) return ($model->status_id == 0) ? 'Не активно' : 'Активно';

                    },


                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}',
                    'buttons' => [
                        'update' => function ($url, $model) {
                            $customurl = Yii::$app->getUrlManager()->createUrl(['rkws/update', 'id' => $model->id]);
                            return \yii\helpers\Html::a('Изменить', $customurl, ['title' => 'Изменить', 'data-pjax' => "0"]);
                        },
                    ]
                ]
            ],
            'options' => ['class' => 'table-responsive'],
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
            'bordered' => false,
            'striped' => true,
            'condensed' => true,
            'responsive' => false,
            'hover' => true,
            'resizableColumns' => false,
            'export' => [
                'fontAwesome' => true,
            ],
        ]);
        ?>
    <?php } ?>

