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
use yii;


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
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/vendorintegr'],
            ],
            'Интеграция с R-keeper WS',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
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
                            'dataProvider' => $dataProvider,
                            'pjax' => true, // pjax is set to always true for this demo
                            //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                            'filterPosition' => false,
                            'columns' => [
                                'denom',
                                'comment',
                                'def_value',
                                /*
                                [
                                    'attribute' => 'vendor.name',
                                    'value' => 'vendor.name',
                                    'label' => 'Поставщик',
                                    //'headerOptions' => ['class'=>'sorting',],
                                ],
                               */

                                [
                                    'value' => function ($data) {

                                        $pConst = \api\common\models\RkPconst::findOne(['const_id' => $data->id, 'org' => Yii::$app->user->identity->organization_id]);

                                        if (isset($pConst)) {
                                            return $pConst->value;
                                        } else {
                                            return $data->def_value;
                                        }
                                    },
                                    'label' => 'Текущее значение',
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'contentOptions'=>['style'=>'width: 6%;'],
                                    'template'=>'{clear}&nbsp;',
                                    'visibleButtons' => [
                                        'clear' => function ($model, $key, $index) {
                                            // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                            return true;
                                        },
                                    ],
                                    'buttons'=>[
                                        'clear' =>  function ($url, $model) {
                                            //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                            $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr\rkws\settings\changeconst', 'id'=>$model->id]);
                                            return \yii\helpers\Html::a( '<i class="fa fa-wrench" aria-hidden="true"></i>', $customurl,
                                                ['title' => 'Изменить значение', 'data-pjax'=>"0"]);
                                        },
                                    ]
                                ],

                            ],
                            /* 'rowOptions' => function ($data, $key, $index, $grid) {
                              return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                              }, */
                            'options' => ['class' => 'table-responsive'],
                            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'bordered' => false,
                            'striped' => true,
                            'condensed' => false,
                            'responsive' => false,
                            'hover' => true,
                            'resizableColumns' => false,
                            'export' => [
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

