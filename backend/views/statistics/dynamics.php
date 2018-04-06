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
                    'value' => function($data) {
                        return 'Оборот:'.$data['w5_sum'].' / Зак-в:'.$data['w5_count'].' / Пост-в:'.$data['w5_vendor'];
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],
                ['format' => 'raw',
                    'attribute' => 'w4_count',
                    'value' => function($data) {
                        return 'Оборот:'.$data['w4_sum'].' / Зак-в:'.$data['w4_count'].' / Пост-в:'.$data['w4_vendor'];
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],
                ['format' => 'raw',
                    'attribute' => 'w3_count',
                    'value' => function($data) {
                        return 'Оборот:'.$data['w3_sum'].' / Зак-в:'.$data['w3_count'].' / Пост-в:'.$data['w3_vendor'];
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],
                ['format' => 'raw',
                    'attribute' => 'w2_count',
                    'value' => function($data) {
                        return 'Оборот:'.$data['w2_sum'].' / Зак-в:'.$data['w2_count'].' / Пост-в:'.$data['w2_vendor'];
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],
                ['format' => 'raw',
                    'attribute' => 'w1_count',
                    'value' => function($data) {
                        return 'Оборот:'.$data['w1_sum'].' / Зак-в:'.$data['w1_count'].' / Пост-в:'.$data['w1_vendor'];
                    },
                    'contentOptions' => ['style' => 'width: 20%;'],
                ],

            ],
        ]);
        ?>

        <?php Pjax::end() ?>
    </div>
</div>
