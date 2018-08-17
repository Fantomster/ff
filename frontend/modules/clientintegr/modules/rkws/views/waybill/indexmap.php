<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use kartik\checkbox\CheckboxX;
use yii\web\JsExpression;
use api\common\models\RkDicconst;


?>
<?php
$sLinkzero = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/makevat', 'waybill_id' => $wmodel->id, 'vat' => 0]);
$sLinkten = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/makevat', 'waybill_id' => $wmodel->id, 'vat' => 1000]);
$sLinkeight = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/makevat', 'waybill_id' => $wmodel->id, 'vat' => 1800]);
$this->registerCss('.table-responsive {overflow-x: hidden;}.alVatFilter{margin-top:-30px;}');
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
    <?php $useAutoVAT = (RkDicconst::findOne(['denom' => 'useTaxVat'])->getPconstValue() != null) ? RkDicconst::findOne(['denom' => 'useTaxVat'])->getPconstValue() : 1; ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    СОПОСТАВЛЕНИЕ НОМЕНКЛАТУРЫ
    <p>
        <span>Контрагент: <?= $agentName ?></span> |
        <span>Номер заказа: <?= $wmodel->order_id ?></span> |
        <span>Номер накладной: <?= $wmodel->num_code ?></span> |
        <span>Склад: <?= $storeName ?></span>
    </p>
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
                                    
                                  //  var output = $("#output"); // блок вывода информации
                                    
                                    $.ajax({
                                                url: "changevat", // путь к php-обработчику
                                                type: "POST", // метод передачи данных
                                            // dataType: "json", // тип ожидаемых данных в ответе
                                                data: {key: this.value + "," + "' . $wmodel->id . '"}, // данные, которые передаем на сервер
                                            /*            
                                          beforeSend: function(){ // Функция вызывается перед отправкой запроса
                                                      output.text("Запрос отправлен. Ждите ответа.");
                                                                }, */
                                                /*                    
                                               error: function(req, text, error){ // отслеживание ошибок во время выполнения ajax-запроса
                                                      output.text("Хьюстон, У нас проблемы! " + text + " | " + error);
                                                                }, */
                                                /*                    
                                            complete: function(){ // функция вызывается по окончании запроса
                                                      output.append("<p>Запрос полностью завершен!</p>");
                                                                }, */
                                                                
                                              success: function(json){ // функция, которая будет вызвана в случае удачного завершения запроса к серверу
                                                      // json - переменная, содержащая данные ответа от сервера. Обзывайте её как угодно ;)
                                                      // output.html(json); // выводим на страницу данные, полученные с сервера
                                                      // $("map_grid1").refresh;
                                                      $.pjax.reload({container:"#map_grid1"}); 
                                                                    }
                                            }); 
                                            // alert(this.value);
                                            }'],
                            ]); ?>
                        </div>
                        <?php
                        $pjax = "$('#search-pjax').on('pjax:end', function(){
                                            $.pjax.reload('#map_grid1',{'timeout':10000});
                                    });";
                        $this->registerJs($pjax);
                        ?>
                        <?php Pjax::begin(['enablePushState' => true, 'timeout' => 10000, 'id' => 'search-pjax']); ?>
                        <?php
                        $form = ActiveForm::begin([
                            'options' => [
                                'data-pjax' => true,
                                'id' => 'search-form',
                                'role' => 'search',
                            ],
                            //'enableClientValidation' => false,
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
                        <?php Pjax::end() ?>
                        <div style="clear: both;"></div>


                        <?php
                        $columns = array(
                            [
                                'attribute' => 'product_id',
                                'label' => 'ID в Mixcart',
                                'vAlign' => 'bottom',
                            ],
                            [
                                'attribute' => 'fproductnameProduct',
                                'label' => 'Наименование продукции',
                                'vAlign' => 'bottom',
                            ],
                            [
                                'attribute' => 'product_id',
                                'value' => function ($model) {
                                    return $model->fproductname->ed ? $model->fproductname->ed : 'Не указано';
                                },
                                'format' => 'raw',
                                'label' => 'Ед. изм. Mixcart',
                                'vAlign' => 'bottom',
                            ],
                            [
                                'class' => 'kartik\grid\EditableColumn',
                                'attribute' => 'pdenom',
                                'label' => 'Наименование в Store House',
                                'vAlign' => 'bottom',
                                'width' => '210px',
                                'refreshGrid' => true,

                                'editableOptions' => [
                                    'asPopover' => $isAndroid ? false : true,
                                    'formOptions' => ['action' => ['edit']],
                                    'header' => 'Продукт R-keeper',
                                    'size' => 'md',
                                    'inputType' => \kartik\editable\Editable::INPUT_SELECT2,
                                    'options' => [
                                        'options' => ['placeholder' => 'Выберите продукт из списка'],
                                        'pluginOptions' => [
                                            'minimumInputLength' => 2,
                                            'ajax' => [
                                                'url' => Url::toRoute('autocomplete'),
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
                                'attribute' => 'munit_rid',
                                'value' => function ($model) {
                                    if (!empty($model->product)) {

                                        return $model->product->unitname;
                                    }
                                    return 'Не задано';
                                },
                                'format' => 'raw',
                                'label' => 'Ед.изм. StoreHouse',
                                'vAlign' => 'bottom',
                            ],
                            [
                                'attribute' => 'defquant',
                                'format' => 'raw',
                                'label' => 'Кол-во в Заказе',
                                'vAlign' => 'bottom',
                            ],
                            [
                                'class' => 'kartik\grid\EditableColumn',
                                'attribute' => 'koef',
                                'refreshGrid' => true,
                                'editableOptions' => [
                                    'asPopover' => $isAndroid ? false : true,
                                    'header' => ':<br><strong>1 единица Mixcart равна:&nbsp; &nbsp;</strong>',
                                    'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                    'afterInput' => function ($form, $w) {
                                        /**
                                         * @var $form ActiveForm
                                         * @var $w \kartik\editable\Editable
                                         */
                                        echo $form->field($w->model, 'enable_all_map')->checkbox();
                                    },
                                    'buttonsTemplate' => '{reset}{submit}',
                                    'resetButton' => [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'icon' => '<i class="glyphicon glyphicon-ban-circle"></i> ',
                                        'name' => 'otkaz',
                                        'label' => 'Отменить'
                                    ],
                                    'submitButton' => [
                                        'class' => 'btn btn-sm btn-success',
                                        'icon' => '<i class="glyphicon glyphicon-save"></i> ',
                                        'name' => 'forever',
                                        'label' => 'Применить сейчас'
                                    ],
                                    'formOptions' => [
                                        'action' => Url::toRoute('changekoef'),
                                        'enableClientValidation' => false,
                                    ],
                                ],
                                'hAlign' => 'right',
                                'vAlign' => 'bottom',
                                'format' => ['decimal', 6],
                                'pageSummary' => true,
                                'label' => 'Коэфф.'
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
                                        'action' => Url::toRoute('changekoef'),
                                        'enableClientValidation' => false,
                                    ],
                                ],
                                'hAlign' => 'right',
                                'vAlign' => 'bottom',
                                // 'width'=>'100px',
                                'format' => ['decimal'],
                                'footer' => 'Итого сумма без НДС:',
                                'pageSummary' => true,
                                'label' => 'Количество'
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
                                        'action' => Url::toRoute('changekoef'),
                                        'enableClientValidation' => false,
                                    ],
                                ],
                                'hAlign' => 'right',
                                'vAlign' => 'bottom',
                                'format' => ['decimal', 2],
                                'footer' => \api\common\models\RkWaybilldata::getSumByWaybillid($wmodel->id),
                                'pageSummary' => true,
                                'label' => 'Сумма б/н'
                            ]);
                        array_push($columns,
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'headerOptions' => ['style' => 'width: 6%; text-align:center'],
                                'contentOptions' => ['style' => 'width: 6%; text-align:center'],
                                'template' => '{zero}&nbsp;{ten}&nbsp;{eighteen}',
                                'header' => '<span align="center">НДС</br>' .
                                    ' <button id="btnZero" type="button" onClick="location.href=\'' . $sLinkzero . '\';" class="btn btn-xs btn-link" style="color:green;">0</button>' .
                                    '<button id="btnTen" type="button" onClick="location.href=\'' . $sLinkten . '\';" class="btn btn-xs btn-link" style="color:green;">10</button>' .
                                    '<button id="btnEight" type="button" onClick="location.href=\'' . $sLinkeight . '\';" class="btn btn-xs btn-link" style="color:green;">18</button></span>',

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

                                        $customurl = Yii::$app->getUrlManager()->createUrl([
                                            'clientintegr/rkws/waybill/chvat',
                                            'id' => $model->id,
                                            'vat' => 0
                                        ]);

                                        return \yii\helpers\Html::a('&nbsp;0', $customurl, [
                                            'title' => Yii::t('backend', '0%'),
                                            'data-pjax' => 0,
                                            'class' => $tClass,
                                            'style' => $tStyle
                                        ]);
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
                                        $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/chvat', 'id' => $model->id, 'vat' => '1000']);
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
                                        $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/chvat', 'id' => $model->id, 'vat' => '1800']);
                                        return \yii\helpers\Html::a('18', $customurl,
                                            ['title' => Yii::t('backend', '18%'), 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle]);
                                    },
                                ]
                            ],
                            [
                                'label' => 'Сумма с НДС',
                                'format' => ['decimal', 2],
                                'hAlign' => 'right',
                                'vAlign' => 'bottom',
                                'value' => function ($model) {
                                    $sumsnds = (1 + ($model->vat) / 10000) * ($model->sum);
                                    return $sumsnds;
                                }
                            ]);

                        array_push($columns,
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'contentOptions' => ['style' => 'width: 6%;'],
                                'template' => '{clear}&nbsp;',
                                'visibleButtons' => [
                                    'clear' => function ($model, $key, $index) {
                                        // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                        return true;
                                    },
                                ],
                                'buttons' => [
                                    'clear' => function ($url, $model) {
                                        //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                        $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/cleardata', 'id' => $model->id]);
                                        return \yii\helpers\Html::a('<i class="fa fa-sign-in" aria-hidden="true"></i>', $customurl,
                                            ['title' => Yii::t('backend', 'Вернуть начальные данные'), 'data-pjax' => "0"]);
                                    },
                                ]
                            ]);
                        ?>
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'pjax' => true, // pjax is set to always true for this demo
                            'pjaxSettings' => ['options' => ['id' => 'map_grid1', 'enablePushState' => false, 'timeout' => 10000]],
                            'filterPosition' => false,
                            'columns' => $columns,
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
                            <?= Html::a('Вернуться',
                                [$this->context->getLastUrl() . 'way=' . $wmodel->order_id],
                                ['class' => 'btn btn-success btn-export']);
                            ?>
                            <?php
                            echo \yii\helpers\Html::a(
                                Html::tag('b', 'Выгрузить накладную',
                                    [
                                        'class' => 'btn btn-success',
                                        'aria-hidden' => true
                                    ]),
                                '#',
                                [
                                    'onclick' => 'return false;',
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
$url = Url::toRoute('waybill/sendws-by-button');
$query_string = Yii::$app->session->get('query_string');
$js = <<< JS
    $(function () {
        
        $(document).on("change", "#vatFilter", function() {
            $("#search-form").submit();
        });
        
        $(' .sendonbutton').on('click', '.export-waybill-btn', function () {
            $('a .export-waybill-btn').click(function(){ return false;});
            var url = '$url';
            var query_string = '$query_string';
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
                                if (data === 'true') {
                                    swal.close();
                                    swal('Готово', '', 'success');
                                    path = document.location.href;
                                    arr = path.split('waybill');
                                    path = arr[0] + 'waybill/index';
                                    if (query_string!='') {path = path+'?'+query_string;}
                                    loc = "document.location.href='"+path+"'";
                                    setTimeout(loc, 1500);
                                } else {
                                    swal(
                                        'Ошибка',
                                        data,
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

