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
use yii\web\JsExpression;
use common\components\Torg12Invoice;

$this->title = 'Интеграция с iiko Office';

$sLinkzero = Url::base(true).Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/waybill/makevat', 'waybill_id' => $wmodel->id,'vat' =>0]);
$sLinkten = Url::base(true).Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/waybill/makevat', 'waybill_id' => $wmodel->id,'vat' =>1000]);
$sLinkeight = Url::base(true).Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/waybill/makevat', 'waybill_id' => $wmodel->id,'vat' =>1800]);
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
                                        data: {key: this.value + "," + "' . $wmodel->id . '"}, // данные, которые передаём на сервер                                                                
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
                            'pjax' => false,
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
                                    'label' => 'Ед. изм. Mixcart',
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
                                        'header' => ':<br><strong>1 единица Mixcart равна:&nbsp; &nbsp;</strong>',
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
                                        'header' => ':<br><strong>Новое количество равно:&nbsp; &nbsp;</strong>',
                                        'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                        'formOptions' => [
                                            'action' => Url::toRoute('change-coefficient'),
                                            'enableClientValidation' => false,
                                        ],
                                    ],
                                    'hAlign' => 'right',
                                    'vAlign' => 'middle',
                                    'format' => ['decimal'],

                                    'pageSummary' => true,
                                    'footer' => 'Итого сумма без НДС:',
                                ],
                                [
                                    'class' => 'kartik\grid\EditableColumn',
                                    'attribute' => 'sum',
                                    'refreshGrid' => true,
                                    'editableOptions' => [
                                        'asPopover' => $isAndroid ? false : true,
                                        'header' => '<strong>Новая сумма равна:&nbsp; &nbsp;</strong>',
                                        'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                        'formOptions' => [
                                            'action' => Url::toRoute('change-coefficient'),
                                            'enableClientValidation' => false,
                                        ],
                                    ],
                                    'hAlign' => 'right',
                                    'vAlign' => 'middle',
                                    'format' => ['decimal', 2],
                                    'pageSummary' => true,
                                    'footer' => Torg12Invoice::getSumWithoutNdsById($wmodel->order_id),
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
                                        return isset($model->vat) ? $model->vat/100 : null;
                                    }
                                ],
                       //
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'contentOptions'=>['style'=>'width: 6%;'],
                                    'template'=>'{zero}&nbsp;{ten}&nbsp;{eighteen}',
                                    // 'header' => '<a class="label label-default" href="setvatz">0</a><a class="label label-default" href="setvatt">10</a><a class="label label-default" href="setvate">18</a>',
                                    'header' => '<span align="center"> <button id="btnZero" type="button" onClick="location.href=\''.$sLinkzero.'\';" class="btn btn-xs btn-link" style="color:green;">0</button>'.
                                        '<button id="btnTen" type="button" onClick="location.href=\''.$sLinkten.'\';" class="btn btn-xs btn-link" style="color:green;">10</button>'.
                                        '<button id="btnEight" type="button" onClick="location.href=\''.$sLinkeight.'\';" class="btn btn-xs btn-link" style="color:green;">18</button></span>',

                                    //  'sort' => false,
                                    //  '' => false,

                                    'visibleButtons' => [
                                        'zero' => function ($model, $key, $index) {
                                            // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                            return true;
                                        },
                                    ],
                                    'buttons'=>[
                                        'zero' =>  function ($url, $model) {

                                            if ($model->vat == 0) {
                                                $tClass = "label label-success";
                                                $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";

                                            } else {
                                                $tClass = "label label-default";
                                                $tStyle = "";
                                            }

                                            //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                            $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/waybill/chvat', 'id'=>$model->id, 'vat' =>0]);
                                            return \yii\helpers\Html::a( '&nbsp;0', $customurl,
                                                ['title' => Yii::t('backend', '0%'), 'data-pjax'=>"0", 'class'=> $tClass, 'style'=>$tStyle]);
                                        },
                                        'ten' =>  function ($url, $model) {

                                            if ($model->vat == 1000) {
                                                $tClass = "label label-success";
                                                $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                            } else {
                                                $tClass = "label label-default";
                                                $tStyle = "";
                                            }

                                            //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                            $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/waybill/chvat', 'id'=>$model->id, 'vat' => '1000']);
                                            return \yii\helpers\Html::a( '10', $customurl,
                                                ['title' => Yii::t('backend', '10%'), 'data-pjax'=>"0", 'class'=> $tClass, 'style'=>$tStyle]);
                                        },
                                        'eighteen' =>  function ($url, $model) {

                                            if ($model->vat == 1800) {
                                                $tClass = "label label-success";
                                                $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                            } else {
                                                $tClass = "label label-default";
                                                $tStyle = "";
                                            }

                                            //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                            $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/waybill/chvat', 'id'=>$model->id, 'vat' => '1800']);
                                            return \yii\helpers\Html::a( '18', $customurl,
                                                ['title' => Yii::t('backend', '18%'), 'data-pjax'=>"0", 'class'=> $tClass, 'style'=>$tStyle]);
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
                                                    Yii::$app->getUrlManager()->createUrl(['clientintegr/iiko/waybill/clear-data', 'id' => $model->id]),
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
                        <div class="sendonbutton">
                        <?php
                                echo Html::a('Вернуться',
                            [$this->context->getLastUrl().'way='.$wmodel->order_id],
                            ['class' => 'btn btn-success btn-export']);
                        ?>
                        <?php
                        echo \yii\helpers\Html::a(
                        Html::tag('b','Выгрузить накладную',
                            [
                            'class' => 'btn btn-success',
                            'aria-hidden' => true
                        ]),
                        '#',
                        [
                            'class' => 'export-waybill-btn',
                            'title' => Yii::t('backend', 'Выгрузить'),
                            'data-pjax' => "0",
                            'data-id' => $wmodel->id,
                            'data-oid' => $wmodel->order_id,
                        ])
                 ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$url = Url::toRoute('waybill/send-by-button');
$js = <<< JS
    $(function () {
        $(' .sendonbutton').on('click', '.export-waybill-btn', function () {
            $('a .export-waybill-btn').click(function(){ return false;});
            var url = '$url';
            var id = $(this).data('id');
            var oid = $(this).data('oid');
            swal({
                title: 'Выполнить выгрузку накладной?',
                type: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Выгрузить',
                cancelButtonText: 'Отмена',
            }).then((result) => {
                if(result.value)
                {
                    swal({
                        title: 'Идёт отправка',
                        text: 'Подождите, пока закончится выгрузка...',
                        onOpen: () => {
                            swal.showLoading();
                            $.post(url, {id:id}, function (data) {
                                if (data.success === true) {
                                    swal.close();
                                    swal('Готово', '', 'success');
                                    path = document.location.href;
                                    arr = path.split('waybill');
                                    path = arr[0] + 'waybill/index';
                                    loc = "document.location.href='"+path+"'";
                                    setTimeout(loc, 1500);
                                    $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
                                } else {
                                    swal(
                                        'Ошибка',
                                        data.error,
                                        'error'
                                    )
                                }
                            })
                            .fail(function() { 
                               swal(
                                    'Ошибка',
                                    'Обратитесь в службу поддержки.',
                                    'error'
                                );
                            });
                        }
                    })
                }
            })
        });
    });
JS;

$this->registerJs($js);
?>