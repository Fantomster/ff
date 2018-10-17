<?php

use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с iiko Office
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr'],
            ],
            'Интеграция с iiko Office',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    <?=
    $this->render('/default/_license_no_active.php', ['lic' => $lic]);
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
                            'dataProvider' => $dataProvider,
                            'pjax' => true, // pjax is set to always true for this demo
                            'filterPosition' => false,
                            'columns' => [
                                [
                                    'label' => 'Свойство',
                                    'attribute' => 'denom'
                                ],
                                [
                                    'label' => 'Комментарий',
                                    'attribute' => 'comment'
                                ],
                                [
                                    'value' => function ($data) {
                                        $model = \api\common\models\iiko\iikoDicconst::findOne(['id' => $data->id]);
                                        $res = $model->getPconstValue();

                                        if ($model->type == \api\common\models\iiko\iikoDicconst::TYPE_PASSWORD) {
                                            return str_pad('', strlen($res), '*');
                                        }

                                        // VAT храним в единицах * 100, нужно облагородить перед выводом.
                                        if ($model->denom == 'taxVat') {
                                            return $res / 100;
                                        }

                                        // В случае отображения логина
                                        if ($model->denom == 'auth_login') {
                                            return $res;
                                        }

                                        // В случае отображения логина
                                        if ($model->denom == 'main_org') {
                                            return $res;
                                        }

                                        // В случае отображения списка доступных складов
                                        if ($model->denom == 'available_stores_list') {
                                            switch ($res) {
                                                case 0:
                                                    return "Все";
                                                default:
                                                    return $res;
                                            }
                                        }

                                        // В случае отображения списка доступных товаров
                                        if ($model->denom == 'available_goods_list') {
                                            switch ($res) {
                                                case 0:
                                                    return "Все";
                                                default:
                                                    return $res;
                                            }
                                        }

                                        if ($model->type == \api\common\models\iiko\iikoDicconst::TYPE_CHECKBOX || $model->type == \api\common\models\iiko\iikoDicconst::TYPE_LIST) {
                                            return $res;
                                        }
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

                                        if (is_numeric($res)) {
                                            return (($res == 1) ? "Включено" : "Выключено");
                                        }

                                        return $res;
                                    },
                                    'label' => 'Текущее значение',
                                    'contentOptions' => ['style' => 'font-weight:bold;'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['style' => 'width: 6%;'],
                                    'template' => '{clear}&nbsp;',
                                    'visibleButtons' => [
                                        'clear' => function ($model, $key, $index) {
                                            return true;
                                        },
                                    ],
                                    'buttons' => [
                                        'clear' => function ($url, $model) {
                                            if ($model->denom == 'main_org') {
                                                return false;
                                            }
                                            if ($model->id == 7) {
                                                $page = Yii::$app->request->post('page');
                                                $sort = Yii::$app->request->post('sort');
                                                if (!$page) {
                                                    $page = 1;
                                                }
                                                if (!$sort) {
                                                    $sort = 'denom';
                                                }
                                                $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/settings/change-const', 'id' => $model->id, 'page' => $page, 'sort' => $sort]);
                                            } else {
                                                $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/settings/change-const', 'id' => $model->id]);
                                            }
                                            return \yii\helpers\Html::a('<i class="fa fa-wrench" aria-hidden="true"></i>', $customurl,
                                                ['title' => 'Изменить значение', 'data-pjax' => "0"]);
                                        },
                                    ]
                                ],

                            ],
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



