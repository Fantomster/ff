<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Modal;
use common\models\Order;

$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        var timer;
        $("body").on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#searchForm").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
        $(".box-body").on("change", "#statusFilter", function() {
            $("#searchForm").submit();
        });
        $("body").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#searchForm").submit();
                }, 700);
            });
    });
        ');
?>

<section class="content-header">
    <h1>
        <i class="fa fa-home"></i>  Заявки ваших ресторанов
        <small>Список заявок подключенных вами ресторанов</small>
    </h1>
</section>
<section class="content">

    <div class="box box-info order-history">
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                        'options' => [
                            'id' => 'searchForm',
                            //'class' => "navbar-form",
                            'role' => 'search',
                        ],
            ]);
            ?>
            <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?=
                            $form->field($searchModel, 'searchString', [
                                'addon' => [
                                    'prepend' => [
                                        'content' => '<i class="fa fa-search"></i>',
                                    ],
                                ],
                                'options' => [
                                    'class' => "margin-right-15 form-group",
                                ],
                            ])
                            ->textInput([
                                'id' => 'searchString',
                                'class' => 'form-control',
                                'placeholder' => 'Поиск'])
                            ->label('Поиск', ['style' => 'color:#555'])
                    ?>
                </div>
                <div class="col-lg-5 col-md-6 col-sm-6">
                        <?= Html::label('Начальная дата / Конечная дата', null, ['style' => 'color:#555']) ?>
                    <div class="form-group" style="width: 300px; height: 44px;">
                        <?=
                        DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'date_from',
                            'attribute2' => 'date_to',
                            'options' => ['placeholder' => 'Дата', 'id' => 'dateFrom'],
                            'options2' => ['placeholder' => 'Конечная дата', 'id' => 'dateTo'],
                            'separator' => '-',
                            'type' => DatePicker::TYPE_RANGE,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy', //'d M yyyy',//
                                'autoclose' => true,
                                'endDate' => "0d",
                            ]
                        ])
                        ?>
                    </div>
                </div>
            </div>
            <?php
            ActiveForm::end();
            Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'vendor-list', 'timeout' => 5000]);
            ?>
            <div class="row">
                <div class="col-md-12">
                    <?=
                    GridView::widget([
                        'id' => 'orderHistory',
                        'dataProvider' => $dataProvider,
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                        'filterModel' => $searchModel,
                        'filterPosition' => false,
                        'summary' => '',
                        'options' => ['class' => 'table-responsive'],
                        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'value' => 'id',
                                'label' => '№',
                            ],
                            'product',
                            [
                                'attribute' => 'categoryName.name',
                                'label' => 'Категория',
                            ],
                            'amount',
                            'comment',
                            [
                                'attribute' => 'client.name',
                                'label' => 'Название ресторана',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function ($data) {
                                    $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                },
                                'label' => 'Дата создания',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} &nbsp; {edit}',
                                'buttons' => [
                                    'view' => function ($url,$model) {
                                        $customurl=Yii::$app->getUrlManager()->createUrl(['site/request','id'=>$model['id']]);
                                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-eye-open"></span>', $customurl,
                                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                                    },
                                    'edit' => function ($url,$model) {
                                        $customurl=Yii::$app->getUrlManager()->createUrl(['site/update-request','id'=>$model['id']]);
                                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-pencil"></span>', $customurl,
                                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                                    },
                                ],
                            ],
                        ],
                    ]);
                    ?>
                </div></div>
<?php Pjax::end() ?>
            <!-- /.table-responsive -->
        </div>
        <!-- /.box-body -->
    </div>
    <?php
    Modal::begin([
        'id' => 'vendorInfo',
    ]);
    ?>
<?php Modal::end(); ?>
</section>
