<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use common\models\Order;
use yii\web\View;
use yii\widgets\ListView;
use kartik\grid\GridView;
use kartik\editable\Editable;
use api\common\models\RkAccess;
use api\common\models\RkWaybill;

?>


<style>
    .bg-default {
        background: #555
    }

    p {
        margin: 0;
    }

    #map {
        width: 100%;
        height: 200px;
    }
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с R-keeper SH (White Server)
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/clientintegr'],
            ],
            'Интеграция с R-keeper WS',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    <?=
    $this->render('/default/_license_no_active.php', ['lic' => $lic, 'licucs' => $licucs]);
    ?>
    Настройки
</section>
<section class="content">
    <div class="catalog-index">

        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">
                        <?=
                        GridView::widget([
                            'dataProvider'     => $dataProvider,
                            'pjax'             => true, // pjax is set to always true for this demo
                            //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                            'filterPosition'   => false,
                            'columns'          => [
                                'denom',
                                'comment',
                                // 'def_value',
                                /*
                                [
                                    'value' => function ($data) {

                                        $model = \api\common\models\RkDicconst::findOne(['id' =>$data->id]);
                                        return ($model->denom == 'taxVat') ? $model->def_value/100 : (($model->def_value == 1) ? "Включено" : "Выключено");
                                    },
                                    'label' => 'Значение по умолчанию',
                                ],
                                */
                                [
                                    'value'          => function ($data) {

                                        $model = \api\common\models\RkDicconst::findOne(['id' => $data->id]);

                                        $res = $model->getPconstValue();

                                        // В случае отображения автоматической выгрузки накладных
                                        if ($model->denom == 'auto_unload_invoice') {
                                            switch ($res) {
                                                case 0:
                                                    return "Выключено";
                                                case 1:
                                                    return "Включено";
                                                case 2:
                                                    return "Полуавтомат";
                                            }
                                        }

                                        $ret = ($model->denom == 'taxVat') ? $res : (($res == 1) ? "Включено" : "Выключено");

                                        if ($model->denom == 'defGoodGroup') $ret = 'Список';

                                        // VAT храним в единицах * 100, нужно облагородить перед выводом. 0/1 конвертим в слова
                                        return $ret;

                                    },
                                    'label'          => 'Текущее значение',
                                    'contentOptions' => ['style' => 'font-weight:bold;'],
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['style' => 'width: 6%;'],
                                    'template'       => '{clear}&nbsp;',
                                    'visibleButtons' => [
                                        'clear' => function ($model, $key, $index) {
                                            // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                            return true;
                                        },
                                    ],
                                    'buttons'        => [
                                        'clear' => function ($url, $model) {
                                            //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                            $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/settings/changeconst', 'id' => $model->id]);
                                            return \yii\helpers\Html::a('<i class="fa fa-wrench" aria-hidden="true"></i>', $customurl,
                                                ['title' => 'Изменить значение', 'data-pjax' => "0"]);
                                        },
                                    ]
                                ],

                            ],
                            /* 'rowOptions' => function ($data, $key, $index, $grid) {
                              return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                              }, */
                            'options'          => ['class' => 'table-responsive'],
                            'tableOptions'     => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter'        => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'bordered'         => false,
                            'striped'          => true,
                            'condensed'        => false,
                            'responsive'       => false,
                            'hover'            => true,
                            'resizableColumns' => false,
                            'export'           => [
                                'fontAwesome' => true,
                            ],
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



