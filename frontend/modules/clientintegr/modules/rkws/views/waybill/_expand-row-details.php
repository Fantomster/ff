<?php

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\Html;
use api\common\models\RkDicconst;

?>
Приходная Накладная:<br><br>

<?php

$waybillMode = RkDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

if (empty($model)) {
    echo "<div  style=\"text-align:right;\">";
    //if ($waybillMode === "0") {
        echo Html::a('Создать накладную', ['create', 'order_id' => $order_id], ['class' => 'btn btn-md fk-button']);
    /*} else {
        echo "Включен автоматический режим создания накладных.";
    }*/
    echo "</div>";
} else {
    $columns = array(
        'id',
        'order_id',
        'text_code',
        'num_code',
        [
            'attribute' => 'corr_rid',
            'value' => function ($model) {
                return (!empty($model->corr->denom)) ? $model->corr->denom : 'Не указано';

            },

        ],
        [
            'attribute' => 'store_rid',
            'value' => function ($model) {
                return (!empty($model->store->name)) ? $model->store->name : 'Не указано';

            },

        ],
        [
            'attribute' => 'doc_date',
            'format' => 'date',

        ],
        'note',
        [
            'attribute' => 'readytoexport',
            'label' => 'К выгрузке',
            'value' => function ($model) {
                return $model->readytoexport ? 'готова' : 'не готова';
            },
        ],
        [
            'attribute' => 'status_id',
            'label' => 'Статус',
            'value' => function ($model) {
                if (isset($model->status)) {
                    return $model->status->denom;
                }

            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'width: 6%;'],
            'template' => '{update}&nbsp;{map}&nbsp;{export}',
            'visibleButtons' => [

                'update' => function ($model, $key, $index) {
                    // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                    return true;
                },
                'map' => function ($model, $key, $index) {
                    // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                    return true;
                },
                'export' => function ($model, $key, $index) {
                    return $model->readytoexport ? true : false;
                },

            ],

            'buttons' => [

                'update' => function ($url, $model) {
                    //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                    $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/update', 'id' => $model->id]);
                    return \yii\helpers\Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', $customurl,
                        ['title' => Yii::t('backend', 'Изменить шапку'), 'data-pjax' => "0"]);
                },
                'map' => function ($url, $model) {
                    //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                    $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/map', 'waybill_id' => $model->id, 'way' => 0, 'RkWaybilldataSearch[vat]' => 1]);
                    return \yii\helpers\Html::a('<i class="fa fa-chain" aria-hidden="true"></i>', $customurl,
                        ['title' => Yii::t('backend', 'Сопоставить'), 'data-pjax' => "0"]);
                },
                'export' => function ($url, $model) {
                    //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                    /*$customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/sendws', 'waybill_id'=>$model->id]);
                    return \yii\helpers\Html::a( '<i class="fa fa-upload" aria-hidden="true"></i>', $customurl,
                        ['title' => Yii::t('backend', 'Выгрузить'), 'data-pjax'=>"0"]);*/

                    return \yii\helpers\Html::a(
                        Html::tag('i', '', [
                            'class' => 'fa fa-upload ',
                            'aria-hidden' => true
                        ]),
                        '#',
                        [
                            'class' => 'export-waybill-btn',
                            'title' => Yii::t('backend', 'Выгрузить'),
                            'data-pjax' => "1",
                            'data-id' => $model->id,
                            'data-oid' => $model->order_id,
                        ]);
                },

            ]

        ]
    );

    $timestamp_now = time();
    ($licucs->status_id == 1)/* && ($timestamp_now <= (strtotime($licucs->td)))*/ ? $lic_rkws_ucs = 1 : $lic_rkws_ucs = 0; // Оставляю строку на случай, что R-Keeper починит правильность указания окончания срока действия своей лицензии
    (($lic->status_id == 1) && ($timestamp_now <= (strtotime($lic->td)))) ? $lic_rkws = 1 : $lic_rkws = 0;
    if (($lic_rkws_ucs == 0) or ($lic_rkws == 0)) {
        unset($columns[10]['buttons']['export']);
    }

    ?>

    <?=
    GridView::widget([
        'dataProvider' => new ActiveDataProvider([
            'query' => $model,
            'sort' => false,
        ]),
        'layout' => '{items}',
        'pjax' => true,
        'id' => 'pjax_user_row_' . $order_id,
        // pjax is set to always true for this demo
        //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
        'filterPosition' => false,
        'columns' => $columns,
        /* 'rowOptions' => function ($data, $key, $index, $grid) {
          return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
          }, */
        'options' => ['class' => 'table-responsive'],
        //  'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
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

