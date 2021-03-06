<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use kartik\form\ActiveForm;
use yii\widgets\Breadcrumbs;
use common\models\Organization;
use common\models\CatalogBaseGoods;
use api_web\components\Registry;

$this->registerJs(

    '
    $(document).ready(function(){
    
        $(document).on("change", "#selectedVendor", function(e) { // реакция на выбор поставщика из выпадающего списка
            var form = $("#searchForm");
            form.submit();
        });
        
        $(document).on("change", "#service_id", function(e) { // реакция на выбор учётного сервиса (интеграции) из выпадающего списка
            var form = $("#searchForm");
            form.submit();
        });

        $(document).on("change keyup paste cut", "#searchString", function() { // реакция на изменение строки в поисковом поле
            $("#hiddenSearchString").val($("#searchString").val());
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(function() {
                $("#searchForm").submit();
            }, 700);
        });
    });
        
        '
);
?>

<img id="cart-image" src="/images/cart.png" style="position:absolute;left:-100%;">
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
        <i class="fa fa-upload"></i> Глобальное сопоставление номенклатуры
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            'Глобальное сопоставление',
        ],
    ])
    ?>

</section>

<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <?php //Pjax::begin(['enablePushState' => true, 'id' => 'fullmapGrid-pjax', 'timeout' => 5000]);
                    ?>
                    <div class="row">
                        <div class="col-md-4" align="left">
                            <div class="guid-header">

                                <?php
                                $form = ActiveForm::begin([
                                    'action'  => Url::to(['index']),
                                    'options' => [
                                        'id'        => 'searchForm',
                                        'data-pjax' => true,
                                        'class'     => "navbar-form no-padding no-margin",
                                        'role'      => 'search',
                                    ],
                                ]);
                                ?>
                                <?=
                                $form->field($searchModel, 'service_id')
                                    ->dropDownList($services, ['id' => 'service_id', 'options' => [$searchModel->service_id => ['selected' => true]]])
                                    ->label(false)
                                ?>
                                <?=
                                $form->field($searchModel, 'searchString', [
                                    'addon'   => [
                                        'append' => [
                                            'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                    'options' => [
                                        'class' => "margin-right-15 form-group",
                                    ],
                                ])
                                    ->textInput([
                                        'id'          => 'searchString',
                                        'class'       => 'form-control',
                                        'placeholder' => Yii::t('message', 'frontend.views.order.search_two', ['ru' => 'Поиск'])
                                    ])
                                    ->label(false)
                                ?>
                                <?=
                                $form->field($searchModel, 'selectedVendor')
                                    ->dropDownList($vendors, ['id' => 'selectedVendor', 'options' => [$selectedVendor => ['selected' => true]]])
                                    ->label(false)
                                ?>
                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>
                        <div class="col-md-5" align="right">
                            <div class="navbar-form no-padding" align="right" style="no-wrap">
                                <?php echo Html::label('Склад:', 'store_set'); ?>
                                <?php echo Html::dropDownList('store_set', null, $stores, ['class' => 'form-control', 'style' => 'width:30%', 'id' => 'store_set']); ?>
                                <?php echo Html::label('Коэф:', 'koef_set'); ?>
                                <?php echo Html::textInput("koef_set", '', ['class' => 'form-control', 'style' => 'width:15%;', 'id' => 'koef_set', 'readonly' => ($searchModel->service_id == \api_web\components\Registry::IIKO_SERVICE_ID && $mainOrg != $client->id)]) ?>
                                <?php echo Html::label('НДС:', 'vat_set'); ?>
                                <?php echo Html::dropDownList('vat_set', null, [-1 => 'Нет', 0 => '0%', 1000 => '10%', 1800 => '18%', 2000 => '20%'],
                                    ['class' => 'form-control', 'style' => 'width:15%', 'id' => 'vat_set']); ?>
                            </div>
                        </div>

                        <div class="col-md-3" align="right">
                            <div class="guid-header">
                                <?= Html::submitButton('<i class="fa fa-check-square-o"></i> Применить', ['class' => 'btn btn-success apply-fullmap']) ?>
                                <?= Html::submitButton('<i class="fa fa-square-o"></i> Очистить весь выбор', ['class' => 'btn btn-success clear-fullmap']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div>

                            <div id="products">
                                <?=
                                GridView::widget([
                                    'dataProvider'   => $dataProvider,
                                    'id'             => 'fullmapGrid',
                                    'filterModel'    => $searchModel,
                                    'filterPosition' => false,
                                    'formatter'      => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                                    //'striped'        => true,
                                    'pjax'           => true,
                                    'pjaxSettings'   => [
                                        'options' => ['timeout' => 30000, 'scrollTo' => true, 'enablePushState' => false]
                                    ],
                                    'toolbar'        => false,
                                    'tableOptions'   => ['class' => 'table table-bordered table-striped dataTable'],
                                    'pager'          => [
                                        'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed
                                    ],
                                    'columns'        => [
                                        [
                                            'contentOptions' => function ($model) {
                                                return ["id"    => "check" . $model['id'],
                                                        'class' => 'small_cell_checkbox width150'];
                                            },
                                            'headerOptions'  => ['style' => 'text-align:center; width150'],
                                            'format'         => 'raw',
                                            'header'         =>
                                                "<input type='checkbox' class='select-on-check-all' name='selection_all' value='0'>",
                                            'value'          => function ($model) {
                                                return "<input type='checkbox' class='checkbox-export kv-row-checkbox' name='selection[]' value='" . $model['id'] . "'>";
                                            }
                                        ],
                                        ['attribute'      => 'service_denom',
                                         'contentOptions' => function ($model) {
                                             return ["id" => "servc" . $model['id']];
                                         },
                                        ],
                                        ['attribute'      => 'id',
                                         'contentOptions' => function ($model) {
                                             return ["id" => "numbr" . $model['id']];
                                         },
                                        ],
                                        [
                                            'format'    => 'raw',
                                            'attribute' => 'product',
                                            'width'     => '400px',
                                            'value'     => function ($data) {
                                                $note = "";
                                                if ($data['article'] !== '#NULL!') {
                                                    $article = $data['article'];
                                                } else {
                                                    $article = '-';
                                                }
                                                $productUrl = Html::a(Html::decode(Html::decode($data['product'])), Url::to(['/order/ajax-show-details', 'id' => $data['id'], 'cat_id' => $data['cat_id']]), [
                                                    'data'  => [
                                                        'target'   => '#showDetails',
                                                        'toggle'   => 'modal',
                                                        'backdrop' => 'static',
                                                    ],
                                                    'id'    => 'catal' . $data['id'],
                                                    'title' => Yii::t('message', 'frontend.views.order.details', ['ru' => 'Подробности']),
                                                ]);
                                                $ed_id = 'ed' . $data['id'];
                                                return "<div class='grid-prod'>" . $productUrl . '</div>' . $note . '<div id="' . $ed_id . '">' . $data['ed'] . "</div><div>" . Yii::t('message', 'frontend.views.order.vendor_two', ['ru' => 'Поставщик:']) . "  "
                                                    . Organization::get_value(CatalogBaseGoods::getSuppById($data['id']))->name . "</div><div class='grid-article'>" . Yii::t('message', 'frontend.views.order.art', ['ru' => 'Артикул:']) . "  <span>"
                                                    . $article . "</span></div>";
                                            },
                                            'label'     => Yii::t('message', 'frontend.fullmap.index.product_name_mixcart', ['ru' => 'Название продукта Mixcart']),
                                        ],
                                        [
                                            'attribute'      => 'pdenom',
                                            'value'          => function ($model) {
                                                return $model['pdenom'] ?? 'Не задано';
                                            },
                                            'label'          => Yii::t('message', 'frontend.fullmap.index.product_name_service', ['ru' => 'Название продукта']),
                                            'vAlign'         => 'middle',
                                            'width'          => '210px',
                                            'contentOptions' => function ($model) {
                                                return ["id" => "prdct" . $model['id']];
                                            },
                                        ],
                                        [
                                            'attribute'      => 'unitname',
                                            'value'          => function ($model) {
                                                if (!empty($model['unitname'])) {
                                                    return $model['unitname'];
                                                }
                                                return 'Не задано';
                                            },
                                            'format'         => 'raw',
                                            'contentOptions' => function ($model) {
                                                return ["id" => "edizm" . $model['id']];
                                            },
                                            'label'          => Yii::t('message', 'frontend.fullmap.index.units_denom', ['ru' => 'Ед. изм.']),
                                        ],
                                        [
                                            'class'           => 'kartik\grid\EditableColumn',
                                            'attribute'       => 'koef',
                                            'refreshGrid'     => false,
                                            'readonly'        => ($searchModel->service_id == Registry::IIKO_SERVICE_ID && $editCan == 0),
                                            'contentOptions'  => function ($model) {
                                                return ["id" => "koeff" . $model['id']];
                                            },
                                            'value'           => function ($model) {
                                                if (!empty($model['koef'])) {
                                                    $koef_old = $model['koef'];
                                                    $koef = str_replace(',', '.', $koef_old);
                                                    $koef_temp = floor($koef * 1000000);
                                                    $koef_len = strlen($koef_temp);
                                                    $koef_left = substr($koef_temp, 0, $koef_len - 6);
                                                    if ($koef_left == '') {
                                                        $koef_left = '0';
                                                    }
                                                    $koef_right = substr($koef_temp, $koef_len - 6);
                                                    return $koef_left . ',' . $koef_right;
                                                }
                                                return '(не задано)';
                                            },
                                            'editableOptions' => [
                                                'name'        => 'koef',
                                                'asPopover'   => true,
                                                'header'      => ':<br><strong>1 единица Mixcart равна:&nbsp; &nbsp;</srong>',
                                                'inputType'   => \kartik\editable\Editable::INPUT_TEXT,
                                                'formOptions' => [
                                                    'action'                 => Url::toRoute(['editkoef', 'service_id' => $searchModel->service_id]),
                                                    'enableClientValidation' => true,
                                                ],
                                            ],
                                            'hAlign'          => 'right',
                                            'vAlign'          => 'middle',
                                            'label'           => Yii::t('message', 'frontend.fullmap.index.koef', ['ru' => 'Коэффициент']),
                                            'pageSummary'     => true
                                        ],
                                        [
                                            'class'           => 'kartik\grid\EditableColumn',
                                            'attribute'       => 'store',
                                            'label'           => Yii::t('message', 'frontend.fullmap.index.store', ['ru' => 'Склад']),
                                            'vAlign'          => 'middle',
                                            'width'           => '210px',
                                            'refreshGrid'     => false,
                                            'contentOptions'  => function ($model) {
                                                return ["id" => "store" . $model['id']];
                                            },
                                            'editableOptions' => [
                                                'asPopover'   => true,
                                                'name'        => 'store',
                                                'data'        => $stores,
                                                'formOptions' => ['action' => ['editstore', 'service_id' => $searchModel->service_id]],
                                                'header'      => 'Склад интеграции',
                                                'size'        => 'md',
                                                'placement'   => "left",
                                                'inputType'   => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                                                'options'     => [
                                                    'options' => ['placeholder' => 'Выберите склад из списка',
                                                    ],
                                                ]
                                            ]
                                        ],
                                        [
                                            'attribute'      => 'vat',
                                            'value'          => function ($model) {
                                                if (isset($model['vat'])) {
                                                    return $model['vat'] / 100;
                                                }
                                                return 'Не задано';
                                            },
                                            'format'         => 'raw',
                                            'contentOptions' => function ($model) {
                                                return ["id" => "nalog" . $model['id']];
                                            },
                                            'label'          => Yii::t('message', 'frontend.fullmap.index.vat', ['ru' => 'Ставка НДС']),
                                        ],
                                        [
                                            'class'          => 'yii\grid\ActionColumn',
                                            'contentOptions' => function ($model) {
                                                return ["id"    => "buttn" . $model['id'],
                                                        'style' => 'width: 6%;',
                                                        'class' => 'vatcl'];
                                            },
                                            'template'       => '{zero}&nbsp;{ten}&nbsp;{eighteen}&nbsp;{twenty}',
                                            'visibleButtons' => [
                                                'zero' => function ($model) {
                                                    return true;
                                                },
                                            ],
                                            'buttons'        => [
                                                'zero'     => function ($model, $data, $index) use ($searchModel) {
                                                    if ($data['vat'] === '0') {
                                                        $tClass = "vatchange btn label label-success";
                                                        $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                    } else {
                                                        $tClass = "vatchange btn label label-default";
                                                        $tStyle = "";
                                                    }
                                                    $tId = 'buttn00' . $data['id'];
                                                    //$customurl = Url::toRoute(['chvat', 'prod_id' => $data['id'], 'vat' => 0, 'service_id' => $searchModel->service_id]);
                                                    return \yii\helpers\Html::button('0',
                                                        ['title' => Yii::t('backend', '0%'), 'id' => $tId, 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle/*, 'url' => $customurl*/]);
                                                },
                                                'ten'      => function ($model, $data, $index) use ($searchModel) {
                                                    if ($data['vat'] == 1000) {
                                                        $tClass = "vatchange btn label label-success";
                                                        $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                    } else {
                                                        $tClass = "vatchange btn label label-default";
                                                        $tStyle = "";
                                                    }
                                                    $tId = 'buttn10' . $data['id'];
                                                    //$customurl = Url::toRoute(['chvat', 'prod_id' => $data['id'], 'vat' => '1000', 'service_id' => $searchModel->service_id]);
                                                    return \yii\helpers\Html::button('10',
                                                        ['title' => Yii::t('backend', '10%'), 'id' => $tId, 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle/*, 'url' => $customurl*/]);
                                                },
                                                'eighteen' => function ($model, $data, $index) use ($searchModel) {
                                                    if ($data['vat'] == 1800) {
                                                        $tClass = "vatchange btn label label-success";
                                                        $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                    } else {
                                                        $tClass = "vatchange btn label label-default";
                                                        $tStyle = "";
                                                    }
                                                    $tId = 'buttn18' . $data['id'];
                                                    //$customurl = Url::toRoute(['chvat', 'prod_id' => $data['id'], 'vat' => '1800', 'service_id' => $searchModel->service_id]);
                                                    return \yii\helpers\Html::button('18',
                                                        ['title' => Yii::t('backend', '18%'), 'id' => $tId, 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle/*, 'url' => $customurl*/]);
                                                },
                                                'twenty'   => function ($model, $data, $index) use ($searchModel) {
                                                    if ($data['vat'] == 2000) {
                                                        $tClass = "vatchange btn label label-success";
                                                        $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                    } else {
                                                        $tClass = "vatchange btn label label-default";
                                                        $tStyle = "";
                                                    }
                                                    $tId = 'buttn20' . $data['id'];
                                                    //$customurl = Url::toRoute(['chvat', 'prod_id' => $data['id'], 'vat' => '2000', 'service_id' => $searchModel->service_id]);
                                                    return \yii\helpers\Html::button('20',
                                                        ['title' => Yii::t('backend', '20%'), 'id' => $tId, 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle/*, 'url' => $customurl*/]);
                                                },
                                            ]
                                        ],
                                    ],
                                ]) ?>
                            </div>

                        </div>
                    </div>
                    <?php // Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?=
Modal::widget([
    'id'            => 'changeQuantity',
    'clientOptions' => false,
])
?>
<?=
Modal::widget([
    'id'            => 'addNote',
    'clientOptions' => false,
])
?>
<?=
Modal::widget([
    'id'            => 'showDetails',
    'clientOptions' => false,
    'size'          => 'modal-lg',
])
?>
<?php
$url_auto_complete_selected_products = Url::toRoute('fullmap/auto-complete-selected-products');
$url_auto_complete_new = Url::toRoute('fullmap/auto-complete-new');
$url_edit_new = Url::toRoute('fullmap/edit-new');
$url_chvat = Url::toRoute('fullmap/chvat');
$url_apply_new = Url::toRoute(['fullmap/apply-fullmap-new']);

$js = <<< JS
    $(function () {
        if (!spisok) {
            var spisok = ',';
        }
        if (!service) {
            var service = 0;
        }
        if (!organization) {
            var organization = '$client->id';
        }
        function links_column6 () { // обрисовка курсивом незаданных значений в столбце "Коэффициент"
            $('[data-col-seq='+6+']').each(function() {
                var id_daddy = $(this).attr('id');
                var cont_child = $('#'+id_daddy+' div button').html();
                if (cont_child=='(не задано)') {
                    $('#'+id_daddy+' div button').each(function() {
                        var editable_link = $(this).hasClass('kv-editable-link');
                        if (editable_link) {
                            $(this).html('<em>' + '(не задано)' + '</em>');
                        }
                    })
                }
            })
        }
        
        function links_column4 () { // реакция на нажатие строки в столбце "Наименование продукта"
            $('[data-col-seq='+4+']').each(function() {
                var idtd = $(this).attr('id');
                var idtds = String(idtd);
                var idnumber = idtds.substring(5);
                var idbutton = 'but' + idnumber;
                var cont_old = $(this).html();
                if (cont_old=='(не задано)') {cont_old='<em>'+cont_old+'</em>';}
                var cont_new = '<button class="button-name" id="'+idbutton+'" style="color:#6ea262;background:none;border:none;border-bottom:1px dashed">'+cont_old+'</button>';
                if (idbutton!='butined') {
                    $(this).html(cont_new);
                }
            });
            $('.button-name').on('click', function () {
                $('a .button-name').click(function(){ return false;});
                var idbutton = $(this).attr('id');
                var idbuttons = String(idbutton);
                var number = idbuttons.substring(3);   // идентификатор строки
                var denom = $("#catal"+number).html(); // наименование товара
                var edizm = $("#ed"+number).html(); // единица измерения товара
                var tovar = denom+'   /'+edizm+'/';    // наименование товара вместе с единицей измерения
                var cont_old = $(this).html();         // содержание ячейки до форматирования
                var nesopost = '<em>(не задано)</em>';   // содержание несопоставленной ячейки
                swal({
                    html: '<span style="font-size:14px">Сопоставить продукт</span></br></br><span id="tovar">товар</span></br></br>' +
                    '<input type="text" id="bukv-tovar" class="swal2-input" placeholder="Введите или выберите товар" autofocus>'+
                    '<div id="bukv-tovar2" style="margin-top:0px;padding-top:0px;"></div>'+'<div id="bukv-tovar3" style="margin-top:0px;padding-top:0px;"></div>'
                    +'<div id="bukv-tovar4" style="margin-top:0px;padding-top:0px;"></div>'
                    + '</br><input type="submit" name="denom_forever" id="denom_forever" class="btn btn-sm btn-primary butsubmit" value="Сопоставить и запомнить"> '
                    + '<input type="button" id="denom_close" class="btn btn-sm btn-outline-danger" value="Отменить">',
                    showConfirmButton:false,
                    inputOptions: new Promise(function (resolve) {
                        $(document).ready ( function(){
                            $("#bukv-tovar").focus();
                            var a = $("#bukv-tovar").val();
                            $("#tovar").html(tovar);
                            if (cont_old!=nesopost)
                            {
                                $("#bukv-tovar").attr( 'placeholder', cont_old);
                            }
                            var us = $('#service_id').val();
                            if (us==2)
                            {
                                var url_auto_complete_selected_products = '$url_auto_complete_selected_products';
                                $.post(url_auto_complete_selected_products).done(
                                    function(data){
                                        if (data!=0) {
                                            $('#bukv-tovar4').html('<i><span style="color:orange">Поиск осуществляется по '+data+' выбранным позициям.</span></i>');
                                        }
                                    }
                                )
                            }
                            var url_auto_complete_new = '$url_auto_complete_new';
                            $.post(url_auto_complete_new, {stroka: a, us:us}).done(
                                function(data){console.log(data);
                                    if (data.length>0) {
                                            var sel100 = 'Показаны первые 100 позиций';
                                            if (data.length>=100) {
                                                $('#bukv-tovar3').html(sel100);
                                            } else {
                                                $('#bukv-tovar3').html('');
                                            }
                                            var sel = '<div id="spisok">';
                                            sel = sel+'<select id="selpos" name="list_tovar" class="swal2-input">';
                                            var index;
                                            for (index = 0; index < data.length; ++index) {
                                                sel = sel+'<option value="'+data[index]['id']+'">'+data[index]['txt']+'</option>';
                                            }
                                            sel = sel+'</select></div>';
                                    } else {
                                        sel = 'Нет данных.';
                                    }
                                    $('#bukv-tovar').css("margin-bottom", "0px");
                                    $('#bukv-tovar2').html(sel);
                                    $('#selpos').css("margin-top", "0px");
                                }
                            );
                            $("#bukv-tovar").keyup(function() {
                                var a = $("#bukv-tovar").val();
                                var url_auto_complete_new = '$url_auto_complete_new';
                                $.post(url_auto_complete_new, {stroka: a, us:us}).done(
                                    function(data){
                                        if (data.length>0) {
                                            var sel100 = 'Показаны первые 100 позиций';
                                            if (data.length>=100) {
                                                $('#bukv-tovar3').html(sel100);
                                            } else {
                                                $('#bukv-tovar3').html('');
                                            }
                                            var sel = '<div id="spisok">';
                                            sel = sel+'<select id="selpos" name="list_postav" class="swal2-input">';
                                            var index;
                                            for (index = 0; index < data.length; ++index) {
                                                sel = sel+'<option value="'+data[index]['id']+'">'+data[index]['txt']+'</option>';
                                            }
                                            sel = sel+'</select></div>';
                                        } else {
                                            sel = 'Нет данных.';
                                        }
                                        $('#bukv-tovar').css("margin-bottom", "0px");
                                        $('#bukv-tovar2').html(sel);
                                        $('#selpos').css("margin-top", "0px");
                                    }
                                );
                            })
                        });
                    })
                });
                $('#denom_close').on('click', function() {
                    swal.close();
                });
                $('#denom_forever').on('click', function () {
                    var selectvalue = $('#selpos').val();
                    var selected_name = $("#selpos option:selected").text();
                    var pos = selected_name.lastIndexOf('(');
                    var selected_name_short = selected_name.substr(0,pos-1);
                    var us = $('#service_id').val();
                    switch (us) {
                        case '1':
                            var us_name = 'R-keeper';
                            break;
                        case '2':
                            var us_name = 'iiko';
                            break;
                        case '8':
                            var us_name = '1С-ресторан';
                            break;
                        case '10':
                            var us_name = 'Tillypad';
                            break;
                    }
                    var koef_zn = $('#koeff'+number+' div .kv-editable-value em').text();
                    var url_edit_new = '$url_edit_new';
                    $.post(url_edit_new, {id:selectvalue, number:number, us:us}, function (data) {
                        $('#edizm'+number).html(data);
                        $('#but'+number).html(selected_name_short);
                        $('#servc'+number).html(us_name);
                        if (koef_zn=='(не задано)') {
                            $('#koeff'+number+' div .kv-editable-value').text('1,000000');
                        }
                        swal.close();
                    })
                });
            });
        }

        $(document).on('pjax:end', function() { // реакция на перерисовку грид-таблицы
            links_column6();
            var edit_can = '$editCan';
            if (edit_can==1) {
                links_column4();
            }
            vatclick();
            variables_parse_first();
        });

        $(document).ready(function() { // действия после полной загрузки страницы
            links_column6();
            var edit_can = '$editCan';
            if (edit_can==1) {
                links_column4();
            }
            vatclick();
            variables_parse_first();
        });
        
        function variables_parse_first() {
            var client = '$client->id';
            var service_current = $('#service_id').val();
            if ((organization != client) || (service != service_current)) {
                organization = client;
                service = service_current;
                spisok = ',';
            }
            $('.kv-row-checkbox').each(function() {
                var temp = ',' + $(this).val() + ',';
                if (spisok.indexOf(temp) != -1) {
                    $(this).prop('checked', true);
                    $(this).parents('tr').addClass('danger');
                }
            });
            var vca = verify_check_all();
            if (vca == 0) {
                $('.select-on-check-all').val(1);
                $('.select-on-check-all').prop('checked', true);
            }
            
        }
        
        function get_spisok() {
            var temp = spisok.length;
            if (spisok == ',') {
                var spisok_new = '';
            } else {
                var spisok_new = spisok.substring(1,temp-1);
            }
            return spisok_new;
        }
        
        function add_product_spisok(product_id) {
            if (spisok.indexOf(','+product_id+',') == -1) {
                spisok = spisok + product_id + ',';
            }    
        }
        
        function del_product_spisok(product_id) {
            var position = ',' + product_id + ',';
            var temp = position.length;
            var pos = spisok.indexOf(position);
            if (pos != -1) {
                var left = spisok.substring(0,pos + 1);
                var right = spisok.substring(pos + temp);
                spisok = left + right;
            }
        }
        
        $(document).on("change", ".kv-row-checkbox", function(e) { // реакция на изменение состояния флажков в крайнем левом столбце
            e.preventDefault();
            var id_row = $(this).val();
            if ($(this).is(':checked')){
                $(this).parents('tr').addClass('danger');
                add_product_spisok(id_row);
                var vca = verify_check_all();
                if (vca == 0) {
                    $('.select-on-check-all').val(1);
                    $('.select-on-check-all').prop('checked', true);
                }
            } else {
                $(this).parents('tr').removeClass('danger');
                del_product_spisok(id_row);
                var vca = verify_check_all();
                if (vca == 1) {
                    $('.select-on-check-all').val(0);
                    $('.select-on-check-all').prop('checked', false);
                }
            }
        });
        
        function verify_check_all () { // функция, возвращающая количество отмеченных галочками флажков
            var count_all_checkbocks_on_page = 0;
            var count_selected_checkbocks_on_page = 0;
            $('.kv-row-checkbox').each(function() {
                count_all_checkbocks_on_page++;
                if ($(this).is(':checked')) {
                    count_selected_checkbocks_on_page++;
                }
            });
            var difference = count_all_checkbocks_on_page - count_selected_checkbocks_on_page;
            return difference;
        }
        
        $(document).on("change", ".select-on-check-all", function(e) { // реакция на изменение состояния флажка в чекбоксе в заголовке крайнего левого столбца
            e.preventDefault();
            if ($(this).is(':checked')) {
                $('.kv-row-checkbox').each(function() {
                    if (!$(this).is('checked')) {
                        $(this).prop('checked', true);
                        var id_row = $(this).val();
                        $(this).parents('tr').addClass('danger');
                        add_product_spisok(id_row);
                    }
                })
            } else {
                $('.kv-row-checkbox').each(function() {
                    $(this).prop('checked', false);
                    var id_row = $(this).val();
                    $(this).parents('tr').removeClass('danger');
                    del_product_spisok(id_row);
                })
            }
        });
        
        $(document).on("click", ".clear-fullmap", function() { // реакция на нажатие кнопки "Очистить весь выбор" 
            spisok = ',';
            $('.kv-row-checkbox').each(function() {
                    $(this).prop('checked', false);
                    var id_row = $(this).val();
                    $(this).parents('tr').removeClass('danger');
            });
            $('.select-on-check-all').val(0);
            $('.select-on-check-all').prop('checked', false);
        });
        
        $(document).on("click", ".apply-fullmap", function() { // реакция на нажатия кнопки "Применить"
            if(spisok == ','){  
                alert("Ничего не выбрано!");
                return false;
            }
            var store_set = $("#store_set").val();
            var koef_set =  $("#koef_set").val();
            var vat_set  =  $("#vat_set").val();
            var service_set  =  $("#service_id option:selected").val();
            if (typeof(koef_set) == "undefined" || koef_set == null || koef_set.length == 0 ) {
                koef_set = -1;
            }
            if ((koef_set != -1) && (koef_set.match(/^[0-9.,]+$/) == null)) {
                alert ("В поле `Коэффициент` введены неправильные символы!");
                return false;
            }
            if (typeof(service_set) == 0 || service_set == null || service_set.length == 0 ) {
                alert ("Не выбран сервис интеграции");
                return false;
            }
            if (store_set == -1 && koef_set == -1 && vat_set == -1) {
                alert ("Не установлен ни один модификатор для массового применения");
                return false;
            }
            var url_apply_new = '$url_apply_new';
            var spisok_string = get_spisok();
            $.post(url_apply_new, {store_set: store_set, koef_set: koef_set, vat_set : vat_set, service_set : service_set, spisok:spisok_string}).done(
                function (data) {
                    $('.kv-row-checkbox').each(function() {
                        var id_row = $(this).val();
                        var position = ',' + id_row + ',';
                        var pos = spisok.indexOf(position);
                        if (pos != -1) {
                            if (store_set != -1) {
                                $('#store' + id_row + ' button').text(data);
                            }
                            if (koef_set != -1) {
                                var koef_end = sixzero(koef_set);
                                /*koef_set = koef_set.replace(',','.');
                                var koef_set_temp = Math.floor(koef_set * 1000000);
                                koef_set_temp = '' + koef_set_temp;
                                var koef_len = koef_set_temp.length;
                                var koef_left = koef_set_temp.substring(0,koef_len-6);
                                if (koef_left == '') {
                                    koef_left = '0';
                                }
                                var koef_right = koef_set_temp.substring(koef_len-6);
                                var koef_end = koef_left + ',' +koef_right;*/
                                $('#koeff' + id_row + ' button').text(koef_end);
                            }
                            if (vat_set != -1) {
                                var vat_number = vat_set / 100;
                                $('#nalog' + id_row).text(vat_number);
                                $('#buttn' + id_row + ' button').removeClass('label-success').addClass('label-default');
                                if (vat_set == 0) {
                                    var vat_end = '00';
                                } else {
                                    var vat_end = vat_number;
                                }
                                $('#buttn'+vat_end+id_row).removeClass('label-default').addClass('label-success');
                            }
                        }
                    })
                }
            )
        });
        
        function vatclick() {
            $('.vatcl').each(function() {
                if ($(this).hasClass('already')===false) {
                    $(this).addClass('already');
                    var td_id = $(this).attr('id');
                    $('#'+td_id+' .vatchange').on('click', function() { // реакция на нажатие кнопок установки ставки НДС в крайнем правом столбце
                        var id_vat_full = $(this).attr('id');
                        var id_vat = id_vat_full.substr(7);
                        var vat_vat = id_vat_full.substr(5,2);
                        var vat_num = vat_vat*100;
                        var serv_id = $('#service_id').val();
                        var url_chvat = '$url_chvat';
                        $.post(url_chvat, {prod_id:id_vat, vat:vat_num, service_id:serv_id}, function (data) {
                            data = JSON.parse(data);
                            if (data['output']==vat_num) {
                                $('#buttn'+vat_vat+id_vat).removeClass('label-default').addClass('label-success');
                                $('#buttn'+vat_vat+id_vat).attr('style','pointer-events: none; cursor: default; text-decoration: none;');
                                if (vat_vat=='00') {
                                    $('#nalog'+id_vat).text('0');
                                } else {
                                    $('#nalog'+id_vat).text(vat_vat);
                                }
                                if (vat_vat!='00') {
                                    if($('#buttn00'+id_vat).hasClass('label-success')) {
                                    $('#buttn00'+id_vat).removeClass('label-success').addClass('vatchange label-default');
                                    $('#buttn00'+id_vat).attr('style','');
                                    }    
                                }
                                if (vat_vat!='10') {
                                    if($('#buttn10'+id_vat).hasClass('label-success')) {
                                        $('#buttn10'+id_vat).removeClass('label-success').addClass('vatchange label-default');
                                        $('#buttn10'+id_vat).attr('style','');
                                    }    
                                }
                                if (vat_vat!='18') {
                                    if($('#buttn18'+id_vat).hasClass('label-success')) {
                                        $('#buttn18'+id_vat).removeClass('label-success').addClass('vatchange label-default');
                                        $('#buttn18'+id_vat).attr('style','');
                                    }    
                                }
                                if (vat_vat!='20') {
                                    if($('#buttn20'+id_vat).hasClass('label-success')) {
                                        $('#buttn20'+id_vat).removeClass('label-success').addClass('vatchange label-default');
                                        $('#buttn20'+id_vat).attr('style','');
                                    }    
                                }
                            } else {
                                console.log(data['message']);
                            }
                        })
                    })
                }
            })
        }
        
        function sixzero(str) {
            str = str.replace(',','.');
            var str_temp = Math.floor(str * 1000000);
            str_temp = '' + str_temp;
            var str_len = str_temp.length;
            var str_left = str_temp.substring(0,str_len-6);
            if (str_left == '') {
                str_left = '0';
            }
            var str_right = str_temp.substring(str_len-6);
            var str_end = str_left + ',' +str_right;
            return str_end;
        }
    });
JS;

$this->registerJs($js);

?>
