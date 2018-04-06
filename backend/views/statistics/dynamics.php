<?php
use yii\widgets\Pjax;
use yii\grid\GridView;

$this->title = implode(' - ',[
    Yii::t('app','Статистика'),
    Yii::t('app','Динамика использования системы в срезе по организациям')
]);
?>
<div class="row">
    <div class="col-md-12">
        <?php
        Pjax::begin(['enablePushState' => false, 'id' => 'ReportList', 'timeout' => 30000]);
        ?>

        <?=
        GridView::widget([
            'dataProvider' => $DataProvider,
            'filterModel' => $SearchModel,
            'filterPosition' => false,
            'pager' => [
                'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed
            ],
            'columns' => [
                 'org_name',
                 'org_id',
                'franchisee_name',
                 'org_contact_name',
                'org_email',
                 'org_city',
                 'org_type',
                 'org_registred',
                 'order_max_date',
                'order_cnt',
                ['format' => 'raw',
                    'attribute' => 'w5_count',
                    'value' => function($model) {
                        if($model->w5_count == 0 || $model->w5_vendor == 0)
                            return 0;
                        return $model->w5_sum / $model->w5_count / $model->w5_vendor;
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],
                ['format' => 'raw',
                    'attribute' => 'w4_count',
                    'value' => function($model) {
                        if($model->w4_count == 0 || $model->w4_vendor == 0)
                            return 0;
                        return $model->w4_sum / $model->w4_count / $model->w4_vendor;
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],
                ['format' => 'raw',
                    'attribute' => 'w3_count',
                    'value' => function($model) {
                        if($model->w3_count == 0 || $model->w3_vendor == 0)
                            return 0;
                        return $model->w3_sum / $model->w3_count / $model->w3_vendor;
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],
                ['format' => 'raw',
                    'attribute' => 'w2_count',
                    'value' => function($model) {
                        if($model->w2_count == 0 || $model->w2_vendor == 0)
                            return 0;
                        return $model->w2_sum / $model->w2_count / $model->w2_vendor;
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],
                ['format' => 'raw',
                    'attribute' => 'w1_count',
                    'value' => function($model) {
                        if($model->w1_count == 0 || $model->w1_vendor == 0)
                            return 0;
                        return $model->w1_sum / $model->w1_count / $model->w1_vendor;
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],

            ],
        ]);
        ?>

        <?php Pjax::end() ?>
    </div>
</div>
