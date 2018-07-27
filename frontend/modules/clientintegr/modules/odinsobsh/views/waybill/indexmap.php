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
use kartik\checkbox\CheckboxX;
use api\common\models\RkAccess;
use api\common\models\RkWaybill;
use yii\web\JsExpression;
use api\common\models\RkDicconst;

$this->title = 'Интеграция с 1С Общепит';

$sLinkzero = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/makevat', 'waybill_id' => $wmodel->id, 'vat' => 0]);
$sLinkten = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/makevat', 'waybill_id' => $wmodel->id, 'vat' => 1000]);
$sLinkeight = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/makevat', 'waybill_id' => $wmodel->id, 'vat' => 1800]);
$this->registerCss('.table-responsive {overflow-x: hidden;}.alVatFilter{margin-top:-30px;}');
?>

    <section class="content-header">
        <h1>
            <i class="fa fa-upload"></i> <?= $this->title ?>
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
                $this->title
            ],
        ])
        ?>
    </section>
    <section class="content-header">
        <?= $this->render('/default/_menu.php'); ?>
        СОПОСТАВЛЕНИЕ НОМЕНКЛАТУРЫ
    </section>
    <section class="content">
        <div class="catalog-index">
            <div class="box box-info">
                <div class="box-header with-border">
                    <div class="panel-body">
                        <div class="box-body table-responsive no-padding">
                            <div style="text-align:center;">
                                <?php echo '<label class="cbx-label" for="s_1">Цены в Заказе включают НДС</label>';
                                echo CheckboxX::widget([
                                    'name' => 's_1',
                                    'value' => $wmodel->vat_included ? 1 : 0,
                                    'options' => ['id' => 's_1'],
                                    'pluginOptions' => ['threeState' => false],
                                    'pluginEvents' => ['change' => 'function() {                                    
                                    $.ajax({
                                        url: "change-vat", // путь к php-обработчику
                                        type: "POST", // метод передачи данных
                                        data: {key: this.value + "," + "' . $wmodel->id . '"}, // данные, которые передаем на сервер                                                                
                                        success: function(json){ // функция, которая будет вызвана в случае удачного завершения запроса к серверу
                                            $.pjax.reload({container:"#map_grid1"}); 
                                        }
                                    }); 
                                }'],
                                ]); ?>
                            </div>
                            <?php
                            $form = ActiveForm::begin([
                                'options' => [
                                    'data-pjax' => true,
                                    'id' => 'search-form',
                                    'role' => 'search',
                                ],
                                'enableClientValidation' => false,
                                'method' => 'get',
                            ]);
                            ?>
                            <div class="row">
                                <div class="col-md-offset-10 col-md-2 alVatFilter">
                                    <?=
                                    $form->field($searchModel, 'vat')
                                        ->dropDownList($vatData, ['id' => 'vatFilter'])
                                        ->label('НДС', ['class' => 'label', 'style' => 'color:#555'])
                                    ?>
                                </div>
                            </div>
                            <?php ActiveForm::end(); ?>
                            <div style="clear: both;"></div>
                            <?php Pjax::begin(['enablePushState' => false, 'id' => 'map_grid1',]); ?>

                            <?=
                            GridView::widget([
                                'dataProvider' => $dataProvider,
                                'pjax' => true,
                                'pjaxSettings' => ['options' => ['id' => 'map_grid1']],
                                'filterPosition' => false,
                                'columns' => [
                                    'product_id',
                                    'fproductnameProduct',
                                    [
                                        'attribute' => 'product_id',
                                        'value' => function ($model) {
                                            return $model->fproductname->ed ? $model->fproductname->ed : 'Не указано';
                                        },
                                        'format' => 'raw',
                                        'label' => 'Ед. изм. mixcart',
                                    ],
                                    [
                                        'class' => 'kartik\grid\EditableColumn',
                                        'attribute' => 'pdenom',
                                        'label' => 'Наименование в 1С Общепит',
                                        'vAlign' => 'middle',
                                        'width' => '210px',
                                        'refreshGrid' => true,

                                        'editableOptions' => [
                                            'asPopover' => $isAndroid ? false : true,
                                            'formOptions' => ['action' => ['edit']],
                                            'header' => 'Продукт 1С Общепит',
                                            'size' => 'md',
                                            'inputType' => \kartik\editable\Editable::INPUT_SELECT2,
                                            'options' => [
                                                'options' => ['placeholder' => 'Выберите продукт из списка'],
                                                'pluginOptions' => [
                                                    'minimumInputLength' => 2,
                                                    'ajax' => [
                                                        'url' => Url::toRoute('auto-complete'),
                                                        'dataType' => 'json',
                                                        'data' => new JsExpression('function(params) { return {term:params.term}; }')
                                                    ],
                                                    'allowClear' => true
                                                ],
                                                'pluginEvents' => [
                                                    "select2:select" => "function() {
                                                        if($(this).val() == 0)
                                                        {
                                                            $('#agent-modal').modal('show');
                                                        }
                                                    }",
                                                ]

                                            ]
                                        ]],
                                    [
                                        'attribute' => 'munit',
                                        'value' => function ($model) {
                                            if (!empty($model->product)) {
                                                return $model->product->measure;
                                            }
                                            return 'Не задано';
                                        },
                                        'format' => 'raw',
                                        'label' => 'Ед.изм. 1С Общепит',
                                    ],
                                    [
                                        'attribute' => 'defquant',
                                        'format' => 'raw',
                                        'label' => 'Кол-во в Заказе',
                                    ],
                                    [
                                        'class' => 'kartik\grid\EditableColumn',
                                        'attribute' => 'koef',
                                        'refreshGrid' => true,
                                        'editableOptions' => [
                                            'asPopover' => $isAndroid ? false : true,
                                            'header' => ':<br><strong>1 единица Mixcart равна:&nbsp; &nbsp;</srong>',
                                            'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                            'formOptions' => [
                                                'action' => Url::toRoute('change-coefficient'),
                                                'enableClientValidation' => false,
                                            ],
                                        ],
                                        'hAlign' => 'right',
                                        'vAlign' => 'middle',
                                        'format' => ['decimal', 6],

                                        'pageSummary' => true
                                    ],
                                    [
                                        'class' => 'kartik\grid\EditableColumn',
                                        'attribute' => 'quant',
                                        'refreshGrid' => true,
                                        'editableOptions' => [
                                            'asPopover' => $isAndroid ? false : true,
                                            'header' => ':<br><strong>Новое количество равно:&nbsp; &nbsp;</srong>',
                                            'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                            'formOptions' => [
                                                'action' => Url::toRoute('change-coefficient'),
                                                'enableClientValidation' => false,
                                            ],
                                        ],
                                        'hAlign' => 'right',
                                        'vAlign' => 'middle',
                                        'format' => ['decimal'],
                                        'footer' => 'Итого сумма без НДС:',
                                        'pageSummary' => true
                                    ],
                                    [
                                        'class' => 'kartik\grid\EditableColumn',
                                        'attribute' => 'sum',
                                        'refreshGrid' => true,
                                        'editableOptions' => [
                                            'asPopover' => $isAndroid ? false : true,
                                            'header' => '<strong>Новая сумма равна:&nbsp; &nbsp;</srong>',
                                            'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                            'formOptions' => [
                                                'action' => Url::toRoute('change-coefficient'),
                                                'enableClientValidation' => false,
                                            ],
                                        ],
                                        'hAlign' => 'right',
                                        'vAlign' => 'middle',
                                        // 'width'=>'100px',
                                        'format' => ['decimal', 2],
                                        'footer' => \api\common\models\one_s\OneSWaybilldata::getSumByWaybillid($wmodel->id),
                                        'pageSummary' => true
                                    ],
                                    [
                                        'attribute' => 'vat',
                                        'format' => 'raw',
                                        'label' => 'НДС',
                                        'contentOptions' => ['class' => 'text-right'],
                                        'value' => function ($model) {
                                            //   $const = \api\common\models\iiko\iikoDicconst::findOne(['denom' => 'taxVat']);
                                            //  if($const) {
                                            //      $result = $const->getPconstValue() / 100;
                                            //  }
                                            return isset($model->vat) ? $model->vat / 100 : null;
                                        }
                                    ],
                                    //
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'contentOptions' => ['style' => 'width: 6%;'],
                                        'template' => '{zero}&nbsp;{ten}&nbsp;{eighteen}',
                                        // 'header' => '<a class="label label-default" href="setvatz">0</a><a class="label label-default" href="setvatt">10</a><a class="label label-default" href="setvate">18</a>',
                                        'header' => '<span align="center"> <button id="btnZero" type="button" onClick="location.href=\'' . $sLinkzero . '\';" class="btn btn-xs btn-link" style="color:green;">0</button>' .
                                            '<button id="btnTen" type="button" onClick="location.href=\'' . $sLinkten . '\';" class="btn btn-xs btn-link" style="color:green;">10</button>' .
                                            '<button id="btnEight" type="button" onClick="location.href=\'' . $sLinkeight . '\';" class="btn btn-xs btn-link" style="color:green;">18</button></span>',

                                        //  'sort' => false,
                                        //  '' => false,

                                        'visibleButtons' => [
                                            'zero' => function ($model, $key, $index) {
                                                // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                                return true;
                                            },
                                        ],
                                        'buttons' => [
                                            'zero' => function ($url, $model) {

                                                if ($model->vat == 0) {
                                                    $tClass = "label label-success";
                                                    $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";

                                                } else {
                                                    $tClass = "label label-default";
                                                    $tStyle = "";
                                                }

                                                //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/chvat', 'id' => $model->id, 'vat' => 0]);
                                                return \yii\helpers\Html::a('&nbsp;0', $customurl,
                                                    ['title' => Yii::t('backend', '0%'), 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle]);
                                            },
                                            'ten' => function ($url, $model) {

                                                if ($model->vat == 1000) {
                                                    $tClass = "label label-success";
                                                    $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                } else {
                                                    $tClass = "label label-default";
                                                    $tStyle = "";
                                                }

                                                //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/chvat', 'id' => $model->id, 'vat' => '1000']);
                                                return \yii\helpers\Html::a('10', $customurl,
                                                    ['title' => Yii::t('backend', '10%'), 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle]);
                                            },
                                            'eighteen' => function ($url, $model) {

                                                if ($model->vat == 1800) {
                                                    $tClass = "label label-success";
                                                    $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                } else {
                                                    $tClass = "label label-default";
                                                    $tStyle = "";
                                                }

                                                //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/chvat', 'id' => $model->id, 'vat' => '1800']);
                                                return \yii\helpers\Html::a('18', $customurl,
                                                    ['title' => Yii::t('backend', '18%'), 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle]);
                                            },
                                        ]
                                    ],
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'contentOptions' => ['style' => 'width: 6%;'],
                                        'template' => '{clear}',
                                        'visibleButtons' => [
                                            'clear' => function ($model, $key, $index) {
                                                return true;
                                            },
                                        ],
                                        'buttons' => [
                                            'clear' => function ($url, $model) {
                                                return \yii\helpers\Html::a(
                                                    '<i class="fa fa-sign-in" aria-hidden="true"></i>',
                                                    Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/clear-data', 'id' => $model->id]),
                                                    [
                                                        'title' => Yii::t('backend', 'Вернуть начальные данные'),
                                                        'data-pjax' => "0"
                                                    ]
                                                );
                                            },
                                        ]
                                    ],
                                ],
                                'showFooter' => true,
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
                            <?php Pjax::end() ?>

                            <?= Html::a('Вернуться',
                                [$this->context->getLastUrl() . 'way=' . $wmodel->order_id],
                                ['class' => 'btn btn-success btn-export']);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
$js = <<< JS
$(function () {

    $(document).on("change", "#vatFilter", function() {
        $("#search-form").submit();
    });

});
JS;

$this->registerJs($js);