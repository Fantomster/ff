<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use kartik\checkbox\CheckboxX;
use yii\web\JsExpression;

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
                                    [
                                        'attribute' => 'product_id',
                                        'vAlign' => 'bottom',
                                    ],
                                    [
                                        'attribute' => 'fproductnameProduct',
                                        'vAlign' => 'bottom',
                                    ],
                                    [
                                        'attribute' => 'product_id',
                                        'value' => function ($model) {
                                            return $model->fproductname->ed ? $model->fproductname->ed : 'Не указано';
                                        },
                                        'format' => 'raw',
                                        'label' => 'Ед. изм. mixcart',
                                        'vAlign' => 'bottom',
                                    ],
                                    [
                                        'class' => 'kartik\grid\EditableColumn',
                                        'attribute' => 'pdenom',
                                        'label' => 'Наименование в 1С Общепит',
                                        'vAlign' => 'bottom',
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
                                            'afterInput' => function($form, $w) {
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
                                                'action' => Url::toRoute('change-coefficient'),
                                                'enableClientValidation' => false,
                                            ],
                                        ],
                                        'hAlign' => 'right',
                                        'vAlign' => 'bottom',
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
                                        'vAlign' => 'bottom',
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
                                            'header' => '<strong>Новая сумма равна:&nbsp; &nbsp;</strong>',
                                            'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                                            'formOptions' => [
                                                'action' => Url::toRoute('change-coefficient'),
                                                'enableClientValidation' => false,
                                            ],
                                        ],
                                        'hAlign' => 'right',
                                        'vAlign' => 'bottom',
                                        'format' => ['decimal', 2],
                                        'footer' => \api\common\models\one_s\OneSWaybilldata::getSumByWaybillid($wmodel->id),
                                        'pageSummary' => true
                                    ],
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
                                        'label' => 'Сумма с НДС',
                                        'format' => ['decimal', 2],
                                        'hAlign' => 'right',
                                        'vAlign' => 'bottom',
                                        'value' => function ($model) {
                                            $sumsnds = (1 + ($model->vat) / 10000) * ($model->sum);
                                            return $sumsnds;
                                        }
                                    ],
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'contentOptions' => ['style' => 'width: 6%;'],
                                        'template' => '{clear}{delete}',
                                        'visibleButtons' => [
                                            'clear' => function ($model, $key, $index) {
                                                return true;
                                            },
                                            'delete' => function ($model, $key, $index) {
                                                return true;
                                            },
                                        ],
                                        'buttons' => [
                                            'clear' => function ($url, $model) {
                                                return \yii\helpers\Html::a(
                                                    '<i class="fa fa-sign-in padding-right-15" aria-hidden="true"></i>',
                                                    Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/waybill/clear-data', 'id' => $model->id]),
                                                    [
                                                        'title' => Yii::t('backend', 'Вернуть начальные данные'),
                                                        'data-pjax' => "0"
                                                    ]
                                                );
                                            },
                                            'delete' => function ($url, $model) {
                                                $text = 'Удалить';
                                                $url = Url::toRoute('waybill/map-trigger-waybill-data-status');
                                                $action = 'delete';
                                                if(!$model->unload_status){
                                                    $action = 'restore';
                                                    $text = 'Восстановить';
                                                }
                                                return \yii\helpers\Html::a(
                                                    '<i class="fa fa-trash" aria-hidden="true"></i>',
                                                    '#',
                                                    [
                                                        'title' => Yii::t('backend', $text),
                                                        'data-pjax' => "0",
                                                        'class' => 'delete-waybill',
                                                        'id' => 'delete-waybill_' . $model->id,
                                                        'data-waybill-id' => $model->id,
                                                        'data-url' => $url,
                                                        'data-product-name' => $model->fproductname->product,
                                                        'data-status' => $model->unload_status,
                                                        'data-action' => $action,
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
                                'rowOptions' => function ($model) {
                                    if(!$model->unload_status) {
                                        return ['style' => 'opacity: 0.3;'];
                                    }
                                },
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
    
	FF = {};
    FF.deleteBtn = {
        init: function(){
            $(document).on('click', '.delete-waybill', function () {
                var that = $(this),
                    url = that.data('url'),
                    id = that.data('waybill-id'),
                    name = that.data('product-name'),
                    action = that.data('action'),
                    status = that.data('status'),
                    title = that.prop('title');
                
                    status = status === 1 ? 0 : 1;
                    
                swal({
	                title: 'Вы точно хотите '+ title.toLowerCase() + ' ' + name +' ?',
	                type: 'info',
	                showCancelButton: true,
	                confirmButtonColor: '#3085d6',
	                cancelButtonColor: '#d33',
	                confirmButtonText: title,
	                cancelButtonText: 'Отмена',
	            }).then((result) => {
	                if(result.value){
	                    $.ajax({
			                url: url,
			                method: 'POST',
			                data:{
			                    id: id,
			                    action: action,
			                    status: status
			                },
			                success: function (data) {
			                	console.log(data);
			                    let el = $('#delete-waybill_' + id);
			                    if(data.success){
			                        if(data.action == 'delete'){
					                    $('tr[data-key='+ id +']').css({opacity: '0.3'});
					                    el.prop('title', 'Восстановить');
					                    el.data('status', 0);
					                    el.data('action', 'restore');
			                        } else if(data.action == 'restore'){
			                            $('tr[data-key='+ id +']').css({opacity: '1'});
					                    el.prop('title', 'Удалить');
					                    el.data('status', 1);
					                    el.data('action', 'delete');
			                        }
			                        
			                    }
			                }
			            });
	                }
	            });
             
            });
        }
    };
    
    FF.deleteBtn.init();
});
JS;

$this->registerJs($js);