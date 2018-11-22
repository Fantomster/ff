<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use kartik\grid\GridView;
// use yii\grid\GridView;
use kartik\form\ActiveForm;
use yii\widgets\Breadcrumbs;
use kartik\widgets\TouchSpin;
use yii\web\View;
use \yii\web\JsExpression;

$js = <<<SCRIPT
/* To initialize BS3 tooltips set this below */
$(function () { 
    $("[data-toggle='tooltip']").tooltip(); 
});;
SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js);

$this->title = Yii::t('message', 'frontend.views.order.set_order', ['ru' => 'Разместить заказ']);

yii\jui\JuiAsset::register($this);

$urlSaveSelected = Url::to(['fullmap/save-selected-maps']);
$urlApply = Url::to(['fullmap/apply-fullmap']);
$urlClear = Url::to(['fullmap/clear-fullmap']);
$selectedCount = count($selected);

if ($client->isEmpty()) { // если у бизнеса не задано имя или местонахождение организации в базе Google
    $endMessage = Yii::t('message', 'frontend.views.request.continue_four', ['ru' => 'Продолжить']);
    $content = Yii::t('message', 'frontend.views.order.hint', ['ru' => 'Чтобы делать заказы, добавьте поставщика!']);
    $suppliersUrl = Url::to(['client/suppliers']);

    frontend\assets\TutorializeAsset::register($this);
    $customJs = <<< JS
                    var _slides = [{
                            title: '&nbsp;',
                            content: '$content',
                            position: 'right-center',
                            overlayMode: 'focus',
                            selector: '.step-vendor',
                        },
                    ];

                    $.tutorialize({
                            slides: _slides,
                            bgColor: '#fff',
                            buttonBgColor: '#84bf76',
                            buttonFontColor: '#fff',
                            fontColor: '#3f3e3e',
                            showClose: true,
                            arrowPath: '/arrows/arrow-green.png',
                            fontSize: '14px',
                            labelEnd: "$endMessage",
                            onStop: function(currentSlideIndex, slideData, slideDom){
                                document.location = '$suppliersUrl';
                            },
                    });

                    if ($(window).width() > 767) {
                        $.tutorialize.start();
                    }

JS;
    $this->registerJs($customJs, View::POS_READY);
}

$this->registerJs(

    'var selectedCount = ' . $selectedCount . ';
    
    $(document).ready(function(){
    
        /*$(document).on("change", "#selectedCategory", function(e) { //? на странице нет элементов с id = selectedCategory
            var form = $("#searchForm");
            form.submit();
            $.post("' . Url::to(['/order/ajax-refresh-vendors']) . '", {"selectedCategory": $(this).val()}).done(function(result) {
                $("#selectedVendor").replaceWith(result);
            });
        });*/
        
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
        
        $("body").on("hidden.bs.modal", "#changeQuantity, #showDetails", function() { //? на странице нет элементов с id = changeQuantity
            $(this).data("bs.modal", null);
        });
        
        $(document).on("click", ".pagination li a", function() { // реакция на нажатия кнопок пагинации
            clearTimeout(timer);
            return true;
        });
        
        $(document).on("change", ".quantity", function(e) { // ? на странице нет элементов с классом quantity
            value = $(this).val();
            $(this).val(value.replace(",", "."));
        });
    });
        
        $(document).on("click", ".apply-fullmap", function(e) { // реакция на нажатия кнопки "Применить"
            if(($("#fullmapGrid").yiiGridView("getSelectedRows").length + selectedCount) == 0){  
                alert("Ничего не выбрано!");
                return false;
            }
            store_set = $("#store_set").val();
            koef_set =  $("#koef_set").val();
            vat_set  =  $("#vat_set").val();
            service_set  =  $("#service_id option:selected").val();
            if (typeof(koef_set) == "undefined" || koef_set == null || koef_set.length == 0 ) koef_set = -1;
            
            //console.log(store_set);
            //console.log(koef_set);
            //console.log(vat_set);
            // Check selection at least one
            
            if (typeof(service_set) == "undefined" || service_set == null || service_set.length == 0 ) {
                alert ("Не выбран сервис интеграции");
                return false;
            }
            
            if (store_set == -1 && koef_set == -1 && vat_set == -1) {
                alert ("Не установлен ни один модификатор для массового применения");
                return false;
            }
            
            url = $(this).attr("href");

            $.ajax({
                url: "' . $urlApply . '",
                type: "POST",
                dataType: "json",
                data: {store_set: store_set, koef_set: koef_set, vat_set : vat_set, service_set : service_set},
                success: function(){
                    selectedCount = 0;
                    $.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
                }
            });
        });
            
            $(document).on("click", ".clear-fullmap", function(e) { // реакция на нажатие кнопки "Очистить весь выбор" 
                if($("#fullmapGrid").yiiGridView("getSelectedRows").length > 0){
                    url = $(this).attr("href");

                    $.ajax({
                        url: "' . $urlClear . '",
                        type: "GET",
                        success: function(){
                            selectedCount = 0;
                            $.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
                        }
                    });
                }
            });
        
          
       /*
            $(document).on("change", ".select-on-check-all", function(e) { // реакция на изменение состояния флажка в чекбоксе в заголовке крайнего левого столбца
   
          //  e.preventDefault();
           // url = $(this).attr("href");
            url      = window.location.href;

            var value = [];
            state = $(this).prop("checked") ? 1 : 0;
            
           $(".checkbox-export").each(function() {
                value.push($(this).val());
            });    

           value = value.toString();  
           
           //console.log(value);
           //console.log(state);
           //console.log(url);
          
           $.ajax({
             url: "' . $urlSaveSelected . '?selected=" +  value+"&state=" + state,
             type: "GET",
             success: function(){
                 $.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
             }
           });
           
    });
    */
    
    /* $(document).on("change", ".checkbox-export", function(e) { // реакция на изменение состояния флажков в крайнем левом столбце
   
           // e.preventDefault();
           // url = $(this).attr("href");
              url      = window.location.href; 

            state = $(this).prop("checked") ? 1 : 0;
            value = $(this).val();    
            
            
            //console.log(value);
            //console.log(state);
            //console.log(url);

           $.ajax({
             url: "' . $urlSaveSelected . '?selected=" +  value+"&state=" + state,
             type: "GET",
             success: function(){
                 //$.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
                // $(#selected_info).val = selectedCount;
                // alert("Good");
             }
           });
           
    });*/
        
        
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
                    <?php // Pjax::begin(['enablePushState' => true, 'id' => 'fullmapGrid-pjax', 'timeout' => 5000]);
                    // ?>
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
                                <?php echo Html::textInput("koef_set", '', ['class' => 'form-control', 'style' => 'width:15%;', 'id' => 'koef_set', 'readonly' => ($searchModel->service_id == 2 && $mainOrg != $client->id)]) ?>
                                <?php echo Html::label('НДС:', 'vat_set'); ?>
                                <?php echo Html::dropDownList('vat_set', null, [-1 => 'Нет', 0 => '0%', 1000 => '10%', 1800 => '18%'],
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
                                    'striped'        => true,
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
                                                            
                                                         //  selectedCount = parseInt($("#selected_info").text());
                                                         //   mode = 1;
                                                                                                                       
                                                        /*    if ((selectedCount+1) > 12 && state == 1) {
                                                            alert("Превышен лимит для выбора продуктов в 300 позиций");
                                                            // mode = 0;
                                                            return false;        
                                                            }
                                                        */    
                                                           $(".checkbox-export").each(function() {
                                                                value.push($(this).val());
                                                            });    
                                                
                                                           value = value.toString();  
                                                           
                                                           $.ajax({
                                                             url: "' . $urlSaveSelected . '?selected=" +  value+"&state=" + state,
                                                             type: "POST",
                                                             data: {selected: value, state: state},
                                                             success: function(data){
                                                             if (data == -1) {
                                                             // alert ("Превышен лимит для выбора продуктов в 300 позиций");
                                                              swal({title: "Ошибка", html:"Превышен лимит для выбора продуктов в 300 позиций", type: "error"});
                                                             }
                                                             $.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
                                                             }
                                                           }); }',
                                                'changeCell' => 'function(e) { 
                                        
                                                             // alert(mode);   
                                                         
                                                          //   if ((typeof(mode) != "undefined") && mode == 1) {
                                                          //   return false;
                                                          //   }
                                                        
                                                            //console.log(selectedCount);
                                                             state = $(this).prop("checked") ? 1 : 0;
                                                            selectedCount = parseInt($("#selected_info").text());
                                                           // alert(selectedCount);
                                                            
                                                          /*  if ((selectedCount+1) > 12 && state == 1) {
                                                            alert("Превышен лимит для выбора продуктов в 300 позиций");
                                                            return false;        
                                                            } */
                                                                                                                      
                                                            url = window.location.href;
                                                            var value = $(this).val();
                                                          
                                                           $.ajax({
                                                             url: "' . $urlSaveSelected . '?selected=" +  value+"&state=" + state,
                                                             type: "POST",
                                                             data: {selected: value, state: state},
                                                             success: function(data){
                                                             if (data == -1) {
                                                             // alert ("Превышен лимит для выбора продуктов в 300 позиций");
                                                              swal({title: "Ошибка", html:"Превышен лимит для выбора продуктов в 300 позиций", type: "error"});
                                                             }
                                                             $.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});                                                             
                                                                
                                                             }
                                                           });}'
                                            ],
                                            'checkboxOptions' => function ($model, $key, $index, $widget) use ($selected) {
                                                return ['value' => $model['id'], 'class' => 'checkbox-export', 'checked' => (in_array($model['id'], $selected)) ? 'checked' : ""];
                                            },
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
                                                    . $data['product'] . "</div><div class='grid-article'>" . Yii::t('message', 'frontend.views.order.art', ['ru' => 'Артикул:']) . "  <span>"
                                                    . $data['article'] . "</span></div>";
                                            },
                                            'label'     => Yii::t('message', 'frontend.fullmap.index.product_name_mixcart', ['ru' => 'Название продукта Mixcart']),
                                        ],
                                        [
                                            'attribute'      => 'pdenom',
                                            'value'          => function ($model) {
                                                if (!empty($model['pdenom'])) {
                                                    return $model['pdenom'];
                                                } else {
                                                    return '(не задано)';
                                                }
                                            },
                                            'value'          => function ($model) {
                                                return $model['pdenom'] ?? 'Не задано';
                                            },
                                            'label'          => Yii::t('message', 'frontend.fullmap.index.product_name_service', ['ru' => 'Название продукта']),
                                            'vAlign'         => 'middle',
                                            'width'          => '210px',
                                            //'refreshGrid' => true,
                                            //'readonly' => ($searchModel->service_id == 2 && $mainOrg != $client->id),
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
                                            'refreshGrid'     => true,
                                            'readonly'        => ($searchModel->service_id == 2 && $editCan == 0),
                                            'contentOptions'  => function ($model) {
                                                return ["id" => "koeff" . $model['id']];
                                            },
                                            'editableOptions' => [
                                                'name'        => 'koef',
                                                'asPopover'   => true,
                                                'header'      => ':<br><strong>1 единица Mixcart равна:&nbsp; &nbsp;</srong>',
                                                'inputType'   => \kartik\editable\Editable::INPUT_TEXT,
                                                'formOptions' => [
                                                    'action'                 => Url::toRoute(['editkoef', 'service_id' => $searchModel->service_id]),
                                                    'enableClientValidation' => false,
                                                ],
                                            ],
                                            'hAlign'          => 'right',
                                            'vAlign'          => 'middle',
                                            'format'          => ['decimal', 6],
                                            'label'           => Yii::t('message', 'frontend.fullmap.index.koef', ['ru' => 'Коэффициент']),
                                            'pageSummary'     => true
                                        ],
                                        [
                                            'class'           => 'kartik\grid\EditableColumn',
                                            'attribute'       => 'store',
                                            'label'           => Yii::t('message', 'frontend.fullmap.index.store', ['ru' => 'Склад']),
                                            'vAlign'          => 'middle',
                                            'width'           => '210px',
                                            'refreshGrid'     => true,
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
                                            'template'       => '{zero}&nbsp;{ten}&nbsp;{eighteen}',
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

$js = <<< JS
    $(function () {
        function links_column4 () { // реакция на нажатие строки в столбце "Наименование продукта"
            $('[data-col-seq='+4+']').each(function() {
                var idtd = $(this).attr('id');
                var idtds = String(idtd);
                var idnumber = idtds.substring(5);
                var idbutton = 'but' + idnumber;
                var cont_old = $(this).html();
                if (cont_old=='(не задано)') {cont_old='<i>'+cont_old+'</i>';}
                var cont_new = '<button class="button-name" id="'+idbutton+'" style="color:#6ea262;background:none;border:none;border-bottom:1px dashed">'+cont_old+'</button>';
                if (idbutton!='butined') {
                    $(this).html(cont_new);
                }
            });
            $('.button-name').on('click', function () {
                $('a .button-name').click(function(){ return false;});
                //var vat_filter = $("#vatFilter").val(); //фильтр НДС
                var idbutton = $(this).attr('id');
                var idbuttons = String(idbutton);
                var number = idbuttons.substring(3);   // идентификатор строки
                var denom = $("#catal"+number).html(); // наименование товара
                var edizm = $("#ed"+number).html(); // единица измерения товара
                //var id = $("#way"+number).html();      // якорь строки
                var tovar = denom+'   /'+edizm+'/';    // наименование товара вместе с единицей измерения
                var cont_old = $(this).html();         // содержание ячейки до форматирования
                var nesopost = '<i>(не задано)</i>';   // содержание несопоставленной ячейки
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
                                function(data){
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
                                                sel = sel+'<option value="'+data[index]['id']+'">'+data[index]['text']+'</option>';
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
                                                sel = sel+'<option value="'+data[index]['id']+'">'+data[index]['text']+'</option>';
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
                })
                $('#denom_close').on('click', function() {
                    swal.close();
                })
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
            var edit_can = '$editCan';
            if (edit_can==1) {
                links_column4();
            }
            vatclick();
        });

        $(document).ready(function() { // действия после полной загрузки страницы
            var edit_can = '$editCan';
            if (edit_can==1) {
                links_column4();
            }
            vatclick();
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
                            } else {
                                console.log(data['message']);
                            }
                        })
                    })
                }
            })
        }        
    });
JS;

$this->registerJs($js);

?>
