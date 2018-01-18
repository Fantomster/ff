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

$this->title = 'Интеграция с iiko Office';

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
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'pjax' => true,
                            'pjaxSettings' => ['options' => ['id' => 'map_grid1']],
                            'filterPosition' => false,
                            'columns' => [
                                'product_id',
                                [
                                    'attribute' => 'product_id',
                                    'value' => function ($model) {
                                        return $model->fproductname->product;
                                    },
                                    'format' => 'raw',
                                    'label' => 'Наименование F-keeper',
                                ],
                                [
                                    'attribute' => 'product_id',
                                    'value' => function ($model) {
                                        return $model->fproductname->ed ? $model->fproductname->ed : 'Не указано';
                                    },
                                    'format' => 'raw',
                                    'label' => 'Ед. изм. F-keeper',
                                ],
                                [
                                    'class' => 'kartik\grid\EditableColumn',
                                    'attribute' => 'pdenom',
                                    'label' => 'Наименование в iiko',
                                    'vAlign' => 'middle',
                                    'width' => '210px',
                                    'refreshGrid' => true,

                                    'editableOptions' => [
                                        'asPopover' => $isAndroid ? false : true,
                                        'formOptions' => ['action' => ['edit']],
                                        'header' => 'Продукт iiko',
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
                                            return $model->product->unit;
                                        }
                                        return 'Не задано';
                                    },
                                    'format' => 'raw',
                                    'label' => 'Ед.изм. iiko',
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
                                        'header' => ':<br><strong>1 единица F-keeper равна:&nbsp; &nbsp;</srong>',
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

                                    'pageSummary' => true
                                ],
                                [
                                    'attribute' => 'vat',
                                    'format' => 'raw',
                                    'label' => 'Ставка НДС',
                                    'contentOptions' => ['class' => 'text-right'],
                                    'value' => function ($model) {
                                        $const = \api\common\models\iiko\iikoDicconst::findOne(['denom' => 'taxVat']);
                                        if($const) {
                                            $result = $const->getPconstValue() / 100;
                                        }
                                        return isset($result) ? $result : null;
                                    }
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
                                                    Yii::$app->getUrlManager()->createUrl(['clientintegr\iiko\waybill\clear-data', 'id' => $model->id]),
                                                    [
                                                        'title' => Yii::t('backend', 'Вернуть начальные данные'),
                                                        'data-pjax' => "0"
                                                    ]
                                            );
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
                        <?= Html::a('Вернуться',
                            ['index'],
                            ['class' => 'btn btn-success btn-export']);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>