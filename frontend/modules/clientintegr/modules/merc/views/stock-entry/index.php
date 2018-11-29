<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
//use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\widgets\DatePicker;

$this->registerCss('
#mercstockentrysearch-is_expiry {
        margin-left: auto;
        margin-right: auto;
        margin-top: 0%;
        left: 40%;
        }
');
?>

<?=
Modal::widget([
    'id'            => 'ajax-load',
    'size'          => 'modal-md',
    'clientOptions' => false,
])
?>

<section class="content-header">
    <h1>
        <img src="<?= Yii::$app->request->baseUrl ?>/img/mercuriy_icon.png" style="width: 32px;">
        <?= Yii::t('message', 'frontend.client.integration.mercury', ['ru' => 'Интеграция с системой ВЕТИС "Меркурий"']) ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru' => 'Интеграция']),
                'url'   => ['/clientintegr/default'],
            ],
            Yii::t('message', 'frontend.client.integration.mercury', ['ru' => 'Интеграция с системой ВЕТИС "Меркурий"']),
        ],
    ])
    ?>
</section>
<section class="content">
    <?=
    $this->render('/default/_license_no_active.php', ['lic' => $lic]);
    ?>
    <?php
    $urlSaveSelected = Url::to(['save-selected-entry']);
    $timestamp_now = time();
    ($lic->status_id == 1) && ($timestamp_now <= (strtotime($lic->td))) ? $lic_merc = 1 : $lic_merc = 0;
    $columns = [
        [
            'class'           => 'common\components\multiCheck\CheckboxColumn',
            'contentOptions'  => function ($model) {
                return ["id"    => "check" . $model['id'],
                        'class' => 'small_cell_checkbox width150'];
            },
            'headerOptions'   => ['style' => 'text-align:center; width150'],
            'onChangeEvents'  => [
                'changeAll'  => 'function(e) {
                                                            url      = window.location.href;
                                                            var value = [];
                                                            state = $(this).prop("checked") ? 1 : 0;
                                                            
                                                           $(".checkbox-export").each(function() {
                                                                value.push($(this).val());
                                                            });    
                                                
                                                           var count = value.length;
                                                           value = value.toString();  
                                                           
                                                           $(\'.inventory_all\').attr(\'disabled\',\'disabled\');
                                                           $(\'.create_vsd\').attr(\'disabled\',\'disabled\');
                                                           $(\'.create_vsd_conversion\').attr(\'disabled\',\'disabled\');
                                                           
                                                           $.ajax({
                                                             url: "' . $urlSaveSelected . '?selected=" +  value+"&state=" + state,
                                                             type: "POST",
                                                             data: {selected: value, state: state},
                                                             success: function(data){
                                                             if(state) {
                                                                selectedCount += count;
                                                             } else {
                                                                selectedCount -= count;
                                                             }
                                                             $.pjax.reload({container: "#vetStoreEntryList-pjax", url: url, timeout:30000});
                                                             }
                                                           }); }',
                'changeCell' => 'function(e) {
                                                             state = $(this).prop("checked") ? 1 : 0;
       
                                                            url = window.location.href;
                                                            var value = $(this).val();
                
                                                            $(\'.inventory_all\').attr(\'disabled\',\'disabled\');
                                                            $(\'.create_vsd\').attr(\'disabled\',\'disabled\');
                                                            $(\'.create_vsd_conversion\').attr(\'disabled\',\'disabled\');
                                                          
                                                           $.ajax({
                                                             url: "' . $urlSaveSelected . '?selected=" +  value+"&state=" + state,
                                                             type: "POST",
                                                             data: {selected: value, state: state},
                                                             success: function(data){
                                                             if(state) {
                                                                selectedCount++;
                                                             } else {
                                                                selectedCount--;
                                                             }
                                                             $.pjax.reload({container: "#vetStoreEntryList-pjax", url: url, timeout:30000});
                                                                
                                                             }
                                                           });}'
            ],
            'checkboxOptions' => function ($model, $key, $index, $widget) use ($selected) {
                return ['value' => $model['id'], 'class' => 'checkbox-export', 'checked' => (in_array($model['id'], $selected)) ? 'checked' : ""];
            },

            /* 'class' => 'yii\grid\CheckboxColumn',
         'contentOptions' => ['class' => 'small_cell_checkbox'],
         'headerOptions' => ['style' => 'text-align:center; '],

         }*/
        ],
        [
            'attribute' => 'entryNumber',
            'format'    => 'raw',
            'value'     => function ($data) {
                return $data['entryNumber'];
            },
        ],
        [
            'attribute' => 'create_date',
            'label'     => Yii::t('message', 'frontend.client.integration.create_date', ['ru' => 'Дата добавления']),
            'format'    => 'raw',
            'value'     => function ($data) {
                return Yii::$app->formatter->asDatetime($data['create_date'], "php:j M Y");
            },
        ],
        [
            'attribute' => 'product_name',
            'label'     => Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']),
            'format'    => 'raw',
            'value'     => function ($data) {
                return $data['product_name'];
            },
        ],
        [
            'attribute' => 'amount',
            'label'     => Yii::t('message', 'frontend.client.integration.volume', ['ru' => 'Объём']),
            'format'    => 'raw',
            'value'     => function ($data) {
                return $data['amount'] . " " . $data['unit'];
            },
        ],
        [
            'attribute' => 'production_date',
            'label'     => Yii::t('message', 'frontend.client.integration.production_date', ['ru' => 'Дата производство']),
            'format'    => 'raw',
            'value'     => function ($data) {
                $res = $data['production_date'];
                try {
                    $res = Yii::$app->formatter->asDatetime($data['production_date'], "php:j M Y");
                } catch (Exception $e) {
                    $res = $data['production_date'];
                }
                return $res;
            },
        ],
        [
            'attribute' => 'expiry_date',
            'label'     => Yii::t('message', 'frontend.client.integration.expiry_date', ['ru' => 'Срок годности']),
            'format'    => 'raw',
            'value'     => function ($data) {
                $res = $data['expiry_date'];
                try {
                    $res = Yii::$app->formatter->asDatetime($data['expiry_date'], "php:j M Y");
                } catch (Exception $e) {
                    $res = $data['expiry_date'];
                }
                return $res;
            },
        ],
        /*[
            'attribute' => 'status',
            'label' => Yii::t('message', 'frontend.views.order.status', ['ru' => 'Статус']),
            'format' => 'raw',
            'value' => function ($data) {
                return '<span class="status">'.\api\common\models\merc\MercStockEntry::$statuses[$data['status']].'</span>';
            },
        ],*/
        /*[
            'attribute' => 'producer_name',
            'label' => Yii::t('message', 'frontend.client.integration.producer_name', ['ru' => 'Производитель']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['product_name'];
            },
        ],*/

        /*[
            'attribute' => 'amount',
            'label' => Yii::t('message', 'frontend.client.integration.volume', ['ru' => 'Объём']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['amount']." ".$data['unit'];
            },
        ],*/
        [
            'attribute' => 'producer_country',
            'label'     => Yii::t('message', 'frontend.client.integration.producer_country', ['ru' => 'Страна происхождения']),
            'format'    => 'raw',
            'value'     => function ($data) {
                return $data['producer_country'];
            },
        ],
        [
            'attribute' => 'producer_name',
            'label'     => Yii::t('message', 'frontend.client.integration.producer_name', ['ru' => 'Производитель']),
            'format'    => 'raw',
            'value'     => function ($data) {
                return $data['producer_name'];
            },
        ],
        /*[
            'attribute' => 'product_marks',
            'label' => Yii::t('message', 'frontend.client.integration.product_marks', ['ru' => 'Маркировка/клеймо']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['product_marks'];
            },
        ],*/
        [
            'class'          => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'width: 7%;'],
            'template'       => '{view}&nbsp;&nbsp;&nbsp;{create}&nbsp;&nbsp;&nbsp;{inventory}',
            'buttons'        => [
                'view'      => function ($url, $model, $key) use ($lic_merc) {
                    $options = [
                        'title'      => Yii::t('message', 'frontend.client.integration.view', ['ru' => 'Просмотр']),
                        'aria-label' => Yii::t('message', 'frontend.client.integration.view', ['ru' => 'Просмотр']),
                        'data'       => [
                            //'pjax'=>0,
                            'target'   => '#ajax-load',
                            'toggle'   => 'modal',
                            'backdrop' => 'static'
                        ],
                        //'data-pjax' => '0',
                    ];
                    $icon = Html::tag('img', '', [
                        'src'   => Yii::$app->request->baseUrl . '/img/view_vsd.png',
                        'style' => 'width: 16px'
                    ]);
                    return Html::a($icon, ['view', 'uuid' => $model->uuid], $options);
                },
                'create'    => function ($url, $model) {
                    $customurl = Url::to(['transport-vsd/step-1', 'selected' => $model->id]);
                    return \yii\helpers\Html::a('<i class="fa fa-truck" aria-hidden="true"></i>', $customurl,
                        ['title' => Yii::t('message', 'frontend.client.integration.store_entry.create_vsd', ['ru' => 'Оформить транспортное ВСД']), 'data-pjax' => "0"]);
                },
                'inventory' => function ($url, $model, $key) use ($searchModel) {
                    $options = [
                        'title'      => Yii::t('message', 'frontend.client.integration.inventory', ['ru' => 'Инвентаризация']),
                        'aria-label' => Yii::t('message', 'frontend.client.integration.inventory', ['ru' => 'Инвентаризация']),
                        'data'       => [
                            //'pjax'=>0,
                            'target'   => '#ajax-load',
                            'toggle'   => 'modal',
                            'backdrop' => 'static',
                        ],
                    ];
                    $icon = Html::tag('img', '', [
                        'src'   => Yii::$app->request->baseUrl . '/img/partial_confirmed.png',
                        'style' => 'width: 24px'
                    ]);
                    return Html::a($icon, ['inventory', 'id' => $model->id], $options);
                },
            ]
        ]
    ];
    ?>
    <?= $this->render('/default/_menu.php', ['lic' => $lic]); ?>
    <h4><?= Yii::t('message', 'frontend.client.integration.mercury.store_entry_list', ['ru' => 'Журнал входной продукци']) ?>
        :</h4>
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php /*Pjax::begin(['id' => 'pjax-vsd-list', 'timeout' => 15000, 'scrollTo' => true, 'enablePushState' => false]); */?>
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-check"></i><?= Yii::t('message', 'frontend.client.integration.mercury.successful', ['ru' => 'Выполнено']) ?>
                            </h4>
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-exclamation-circle"></i><?= Yii::t('message', 'frontend.views.vendor.error', ['ru' => 'Ошибка']) ?>
                            </h4>
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>
                    <?=
                    Html::a('<i class="fa fa-plus" style="margin-top:-3px;"></i><span class="hidden-sm hidden-xs"> Добавление входной продукции на предприятие  </span>', ['create'], [
                        'class'     => 'btn btn-success',
                        'data-pjax' => 0,
                    ]);
                    ?>
                    <?php
                    $form = ActiveForm::begin([
                        'options'                => [
                            'data-pjax' => true,
                            'id'        => 'search-form',
                            'role'      => 'search',
                        ],
                        'enableClientValidation' => false,
                        //'method'                 => 'get',
                    ]); ?>
                    <div class="col-md-12">
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                $form->field($searchModel, "product_name", [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                ])
                                    ->textInput(['prompt' => Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']), 'class' => 'form-control', 'id' => 'product_name'])
                                    ->label(Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']), ['class' => 'label search_string', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                $form->field($searchModel, "producer_name", [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                ])
                                    ->textInput(['prompt' => 'Производитель', 'class' => 'form-control', 'id' => 'producer_name'])
                                    ->label('Производитель', ['class' => 'label search_string', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <?= Html::label(Yii::t('message', 'frontend.client.integration.production_date', ['ru' => 'Дата производства']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                            <div class="form-group" style="height: 44px;">
                                <?=
                                DatePicker::widget([
                                    'model'         => $searchModel,
                                    'attribute'     => 'date_from_production_date',
                                    'attribute2'    => 'date_to_production_date',
                                    'options'       => ['placeholder' => Yii::t('message', 'frontend.views.order.date', ['ru' => 'Дата']), 'id' => 'dateFromProductionDate'],
                                    'options2'      => ['placeholder' => Yii::t('message', 'frontend.views.order.date_to', ['ru' => 'Конечная дата']), 'id' => 'dateToProductionDate'],
                                    'separator'     => '-',
                                    'type'          => DatePicker::TYPE_RANGE,
                                    'pluginOptions' => [
                                        'orientation' => 'bottom left',
                                        'format'      => 'dd.mm.yyyy', //'d M yyyy',//
                                        'autoclose'   => true,
                                        'endDate'     => "0d",
                                    ]
                                ])
                                ?>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <?= Html::label(Yii::t('message', 'frontend.client.integration.expiry_date', ['ru' => 'Срок годности']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                            <div class="form-group" style="height: 44px;">
                                <?=
                                DatePicker::widget([
                                    'model'         => $searchModel,
                                    'attribute'     => 'date_from_expiry_date',
                                    'attribute2'    => 'date_to_expiry_date',
                                    'options'       => ['placeholder' => Yii::t('message', 'frontend.views.order.date', ['ru' => 'Дата']), 'id' => 'dateFromExpiryDate'],
                                    'options2'      => ['placeholder' => Yii::t('message', 'frontend.views.order.date_to', ['ru' => 'Конечная дата']), 'id' => 'dateToExpiryDate'],
                                    'separator'     => '-',
                                    'type'          => DatePicker::TYPE_RANGE,
                                    'pluginOptions' => [
                                        'orientation' => 'bottom left',
                                        'format'      => 'dd.mm.yyyy', //'d M yyyy',//
                                        'autoclose'   => true,
                                        'endDate'     => "0d",
                                    ]
                                ])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3 col-md-2 col-lg-1">
                            <?= Html::label('Просрочено', null, ['class' => 'label', 'style' => 'color:#555']) ?>
                            <?=
                            $form->field($searchModel, "is_expiry", ['template' => '{input}{error}'])
                                ->checkbox([], false)->label(false);
                            ?>
                        </div>
                        <div class="col-sm-3 col-md-2 col-lg-1">
                            <?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
                            <?= Html::button('<i class="fa fa-times" aria-hidden="true"></i>', ['class' => 'form-control clear_filters btn btn-outline-danger teaser']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                    <div class="col-md-12">
                        <?php
                        //$checkBoxColumnStyle = ($searchModel->type == 2) ? "display: none;" : "";
                        echo GridView::widget([
                            'id'           => 'vetStoreEntryList',
                            'pjax' => true,
                            'dataProvider' => $dataProvider,
                            'formatter'    => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                            //'filterModel' => $searchModel,
                            //'filterPosition' => false,
                            'summary'      => '',
                            'options'      => ['class' => ''],
                            'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                            'columns'      => $columns
                        ]);
                        ?>
                    </div>
                    <?php /*Pjax::end();*/ ?>
                    <?= '<div class="col-md-3">' . Html::submitButton(Yii::t('app', 'frontend.client.integration.store_entry.inventory_all', ['ru' => 'Списать все']), ['class' => 'btn btn-danger inventory_all']) . '</div>' ?>
                    <?= '<div class="col-md-3">' . Html::submitButton(Yii::t('message', 'frontend.client.integration.store_entry.create_vsd', ['ru' => 'Оформить транспортное ВСД']), ['class' => 'btn btn-success create_vsd']) . '</div>' ?>
                    <?= '<div class="col-md-3">' . Html::submitButton(Yii::t('app', 'frontend.client.integration.store_entry.conversion', ['ru' => 'Переработка']), ['class' => 'btn btn-primary create_vsd_conversion']) . '</div>' ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$urlCreateVSD = Url::to(['transport-vsd/step-1']);
$urlCreateVSDConversion = Url::to(['transport-vsd/conversion-step-1']);
$loading = Yii::t('message', 'frontend.client.integration.loading', ['ru' => 'Загрузка']);
$urlInventoryVSD = Url::to(['inventory-all']);
$selectedCount = count($selected);
$customJs = <<< JS
var selectedCount = $selectedCount;
var justSubmitted = false;

$(document).on('pjax:complete', function() {
    if(selectedCount > 0) {
        $('.inventory_all').removeAttr('disabled');
        $('.create_vsd').removeAttr('disabled');
        $('.create_vsd_conversion').removeAttr('disabled');
    }
});

$(document).on("click", ".create_vsd", function(e) {
        if(($("#vetStoreEntryList").yiiGridView("getSelectedRows").length + selectedCount) > 0){
            window.location.href =  "$urlCreateVSD";  
        }
    });

$(document).on("click", ".create_vsd_conversion", function(e) {
        if(($("#vetStoreEntryList").yiiGridView("getSelectedRows").length + selectedCount) > 0){
            window.location.href =  "$urlCreateVSDConversion";  
        }
    });

$(document).on("click", ".inventory_all", function(e) {
       if(($("#vetStoreEntryList").yiiGridView("getSelectedRows").length + selectedCount) > 0){
            window.location.href =  "$urlInventoryVSD";
        }
    });

$("body").on("show.bs.modal", "#ajax-load", function() {
    $(this).data("bs.modal", null);
    var modal = $(this);
    modal.find('.modal-content').html(
    "<div class=\"modal-header\">" + 
    "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">×</button>" + 
    "</span><h4 class=\"modal-title\"><span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span>$loading</h4></div>");
});


$(".modal").removeAttr("tabindex");

$("body").on("hidden.bs.modal", "#ajax-load", function() {
    $(this).data("bs.modal", null);
});

$("#ajax-load").on("click", ".save-form", function() {
    var form = $("#ajax-form");
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            $.pjax.reload("#vetStoreEntryList-pjax", {timeout:30000});
            if(result != true)    
                form.replaceWith(result);
            else
                $("#ajax-load .close").click();
        });
        return false;
    });

 /*$("document").ready(function(){
        $(".box-body").on("change", "#statusFilter", function() {
            $("#search-form").submit();
        });
     }); 
 
  $("document").ready(function(){
        $(".box-body").on("change", "#typeFilter", function() {
            $("#search-form").submit();
        });
     });  
 */
 $("document").ready(function(){
        $(".box-body").on("change", "#mercstockentrysearch-is_expiry", function() {
            $("#search-form").submit();
        });
     });   
 
 $(document).on("click", ".clear_filters", function () {
           $('#product_name').val(''); 
           $('#producer_name').val('');
           $('#dateFromProductionDate').val('');
           $('#dateToProductionDate').val('');
           $('#dateFromExpiryDate').val('');
           $('#dateToExpiryDate').val('');
           $('#mercstockentrysearch-is_expiry').prop('checked', false);
           $("#search-form").submit();
    });
 
 $(".box-body").on("change", "#dateFromProductionDate, #dateToProductionDate", function() {
            if (!justSubmitted) {
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
 
  $(".box-body").on("change", "#dateFromExpiryDate, #dateToExpiryDate", function() {
            if (!justSubmitted) {
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
 
 $(document).on("change keyup paste cut", "#product_name", function() {
     if (justSubmitted) {
            clearTimeout(justSubmitted);
        }
        justSubmitted = setTimeout(function() {
            justSubmitted = false;
            $("#search-form").submit();
        }, 700);
    });
  $(document).on("change keyup paste cut", "#producer_name", function() {
     if (justSubmitted) {
            clearTimeout(justSubmitted);
        }
        justSubmitted = setTimeout(function() {
            justSubmitted = false;
            $("#search-form").submit();
        }, 700);
    });
JS;
$this->registerJs($customJs, View::POS_READY);
?>

