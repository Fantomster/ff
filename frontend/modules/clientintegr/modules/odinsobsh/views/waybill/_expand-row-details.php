<?php

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use api\common\models\one_s\OneSDicconst;

?>

Приходная Накладная:<br><br>

<?php

$waybillMode = OneSDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

if (empty($model)) {
    echo "<div  style=\"text-align:right;\">";
    if ($waybillMode === "0") {
        echo Html::a('Создать накладную', ['create', 'order_id' => $order_id, 'page' => $page], ['class' => 'btn btn-md fk-button']);
    } else {
        echo "Включен автоматический режим создания накладных.";
    }
    echo "</div>";
} else {
    $columns = [
        'id',
        'order_id',
        'num_code',
        [
            'attribute' => 'agent_uuid',
            'value'     => function ($model) {
                return (!empty($model->agent->name)) ? $model->agent->name : 'Не указано';

            },
        ],
        [
            'attribute' => 'store_id',
            'value'     => function ($model) {
                return (!empty($model->store->name)) ? $model->store->name : 'Не указано';

            },
        ],
        [
            'attribute' => 'doc_date',
            'format'    => 'date',
        ],
        'note',
        [
            'attribute' => 'readytoexport',
            'label'     => 'К выгрузке',
            'value'     => function ($model) {
                return $model->readytoexport ? 'готова' : 'не готова';
            },
        ],
        [
            'attribute' => 'status_id',
            'label'     => 'Статус',
            'value'     => function ($model) {
                if (isset($model->status)) {
                    return $model->status->denom;
                }
            },
        ],
        [
            'class'          => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'width: 6%;'],
            'template'       => '{update}&nbsp;{map}&nbsp;{export}',
            'visibleButtons' => [
                'update' => function ($model, $key, $index) {
                    return true;
                },
                'map'    => function ($model, $key, $index) {
                    return true;
                },
            ],
            'buttons'        => [

                'update' => function ($url, $model) {
                    $page = Yii::$app->request->get('page');
                    if ($page == '') {
                        $page = 1;
                    }
                    $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/update', 'id' => $model->id, 'page' => $page]);
                    return \yii\helpers\Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', $customurl,
                        ['title' => Yii::t('backend', 'Изменить шапку'), 'data-pjax' => "0"]);
                },
                'map'    => function ($url, $model) {
                    $page = Yii::$app->request->get('page');
                    if ($page == '') {
                        $page = 1;
                    }
                    $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/map', 'waybill_id' => $model->id, 'way' => 0, 'OneSWaybillDataSearch[vat]' => 1, 'page' => $page]);
                    return \yii\helpers\Html::a('<i class="fa fa-chain" aria-hidden="true"></i>', $customurl,
                        ['title' => Yii::t('backend', 'Сопоставить'), 'data-pjax' => "0"]);
                },
            ]
        ]
    ];
    $timestamp_now = time();
    if (!(($lic->status_id == 1) && ($timestamp_now <= (strtotime($lic->td))))) {
        unset($columns[10]['buttons']['export']);
    }

    echo GridView::widget([
        'dataProvider'     => new ActiveDataProvider([
            'query' => $model,
            'sort'  => false,
        ]),
        'layout'           => '{items}',
        'pjax'             => true,
        'id'               => 'pjax_user_row_' . $order_id,
        'filterPosition'   => false,
        'columns'          => $columns,
        'options'          => ['class' => 'table-responsive'],
        'formatter'        => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
        'bordered'         => false,
        'striped'          => true,
        'condensed'        => true,
        'responsive'       => false,
        'hover'            => true,
        'resizableColumns' => false,
        'export'           => [
            'fontAwesome' => true,
        ],
    ]);
    ?>
<?php } ?>

