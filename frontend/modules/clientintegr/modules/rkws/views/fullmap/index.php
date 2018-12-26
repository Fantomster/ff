<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use kartik\form\ActiveForm;
use yii\widgets\Breadcrumbs;
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

if ($client->isEmpty()) {
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

    '
    var cnt = ' . $selectedCount . ';
    
    $(document).ready(function(){
    
     
     
            $(document).on("change", "#selectedCategory", function(e) {
                var form = $("#searchForm");
                form.submit();
                $.post(
                    "' . Url::to(['/order/ajax-refresh-vendors']) . '",
                    {"selectedCategory": $(this).val()}
                ).done(function(result) {
                    $("#selectedVendor").replaceWith(result);
                });
            });
            $(document).on("change", "#selectedVendor", function(e) {
                var form = $("#searchForm");
                form.submit();
            });
            $(document).on("click", ".add-to-cart", function(e) {
                e.preventDefault();
                quantity = $(this).parent().parent().find(".quantity").val();
                var cart = $(".basket_a");
                var imgtodrag = $("#cart-image");
                if (imgtodrag) {
                    var imgclone = imgtodrag.clone()
                        .offset({
                        top: $(this).offset().top - 30,
                        left: $(this).offset().left + 60
                    })
                        .css({
                        "opacity": "0.5",
                            "position": "absolute",
                            "height": "60px",
                            "width": "60px",
                            "z-index": "10000"
                    })
                        .appendTo($("body"))
                        .animate({
                        "top": cart.offset().top,
                            "left": cart.offset().left,
                            "width": 60,
                            "height": 60
                    }, 1000, "easeInOutExpo");

                    setTimeout(function () {
                        cart.parent().effect("highlight", {
                            times: 2,
                            color: "#6ea262"
                        }, 350);
                    }, 1000);

                    imgclone.animate({
                        "width": 0,
                            "height": 0
                    }, function () {
                        $(this).detach()
                    });
                }
                $.post(
                    "' . Url::to(['/order/ajax-add-to-cart']) . '",
                    {"id": $(this).data("id"), "quantity": quantity, "cat_id": $(this).data("cat")}
                ).done(function(result) {
                   $(\'a[data-id="\'+result+\'"]\').parent().parent().addClass("success");
                });
            });
            
            $(document).on("change keyup paste cut", "#searchString", function() {
                $("#hiddenSearchString").val($("#searchString").val());
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#searchForm").submit();
                }, 700);
            });
            $("body").on("hidden.bs.modal", "#changeQuantity, #showDetails", function() {
                $(this).data("bs.modal", null);
            });
            $(document).on("click", ".pagination li a", function() {
                clearTimeout(timer);
                return true;
            });
            $(document).on("change", ".quantity", function(e) {
                value = $(this).val();
                $(this).val(value.replace(",", "."));
            });
        });
        
            $(document).on("click", ".apply-fullmap", function(e) {
                       
             if(($("#fullmapGrid").yiiGridView("getSelectedRows").length + cnt) == 0){  
             alert("Ничего не выбрано!");
             return false;
             }
            
            store_set = $("#store_set").val();
            koef_set =  $("#koef_set").val();
            vat_set  =  $("#vat_set").val();
            
            if (typeof(koef_set) == "undefined" || koef_set == null || koef_set.length == 0 )
            koef_set = -1;
            
            // console.log(store_set);
            // console.log(koef_set);
            // console.log(vat_set);
            
            // Check selection at least one
            
            if (store_set == -1 && koef_set == -1 && vat_set == -1) {
            alert ("Не установлен ни один модификатор для массового применения");
            return false;
            }
            
            url = $(this).attr("href");

           $.ajax({
             url: "' . $urlApply . '",
             type: "POST",
             dataType: "json",
             data: {store_set: store_set, koef_set: koef_set, vat_set : vat_set},
             success: function(){
                 cnt = 0;
                 $.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
             }
           });
            
            });
            
            $(document).on("click", ".clear-fullmap", function(e) {
             if($("#fullmapGrid").yiiGridView("getSelectedRows").length > 0){
            
            url = $(this).attr("href");

           $.ajax({
             url: "' . $urlClear . '",
             type: "GET",
             success: function(){
                 cnt = 0;
                 $.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
             }
           });
            }
            });
        
          
       
            $(document).on("change", ".select-on-check-all", function(e) {
   
          //  e.preventDefault();
           // url = $(this).attr("href");
            url      = window.location.href;

            var value = [];
            state = $(this).prop("checked") ? 1 : 0;
            
           $(".checkbox-export").each(function() {
                value.push($(this).val());
            });    

           value = value.toString();  
           
           console.log(value);
           console.log(state);
           console.log(url);
          
           $.ajax({
             url: "' . $urlSaveSelected . '?selected=" +  value+"&state=" + state,
             type: "GET",
             success: function(){
                 $.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
             }
           });
           
    });
    
     $(document).on("change", ".checkbox-export", function(e) {
   
           // e.preventDefault();
           // url = $(this).attr("href");
              url      = window.location.href; 

            state = $(this).prop("checked") ? 1 : 0;
            value = $(this).val();    
            
            
             console.log(value);
             console.log(state);
             console.log(url);

           $.ajax({
             url: "' . $urlSaveSelected . '?selected=" +  value+"&state=" + state,
             type: "GET",
             success: function(){
                 //$.pjax.reload({container: "#fullmapGrid-pjax", url: url, timeout:30000});
             }
           });
           
    });
        
        
        '
);
?>
<?php
$sLinkzero = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/fullmap/makevat', 'vat' => 0]);
$sLinkten = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/fullmap/makevat', 'vat' => 1000]);
$sLinkeight = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/fullmap/makevat', 'vat' => 1800]);
$sLinktwenty = Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/fullmap/makevat', 'vat' => 2000]);
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
        <i class="fa fa-upload"></i> Интеграция с R-keeper SH (White Server)
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/clientintegr'],
            ],
            'Интеграция с R-keeper WS',
        ],
    ])
    ?>
    <?php // $useAutoVAT            = (RkDicconst::findOne(['denom' => 'useTaxVat'])->getPconstValue() != null) ? RkDicconst::findOne(['denom' => 'useTaxVat'])->getPconstValue() : 1; ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>

    <?=
    $this->render('/default/_license_no_active.php', ['lic' => $lic, 'licucs' => $licucs]);
    ?>

    ГЛОБАЛЬНОЕ СОПОСТАВЛЕНИЕ НОМЕНКЛАТУРЫ
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <?php // Pjax::begin(['enablePushState' => true, 'id' => 'fullmapGrid-pjax', 'timeout' => 5000]); ?>
                    <div class="row">
                        <div class="col-md-4" align="left">
                            <div class="guid-header">

                                <?php
                                $form = ActiveForm::begin([
                                    'action'  => Url::to('index'),
                                    'options' => [
                                        'id'        => 'searchForm',
                                        'data-pjax' => true,
                                        'class'     => "navbar-form no-padding no-margin",
                                        'role'      => 'search',
                                    ],
                                ]);
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
                                <?php echo Html::textInput("koef_set", '', ['class' => 'form-control', 'style' => 'width:15%;', 'id' => 'koef_set']) ?>
                                <?php echo Html::label('НДС:', 'vat_set'); ?>
                                <?php echo Html::dropDownList('vat_set', null, [-1 => 'Нет', 0 => '0%', 1000 => '10%', 1800 => '18%', 2000 => '20%'],
                                    ['class' => 'form-control', 'style' => 'width:15%', 'id' => 'vat_set']); ?>
                            </div>
                        </div>

                        <div class="col-md-3" align="right">
                            <div class="guid-header">
                                <?= Html::submitButton('<i class="fa fa-th"></i> Применить', ['class' => 'btn btn-success apply-fullmap']) ?>
                                <?= Html::submitButton('<i class="fa fa-th"></i> Очистить буфер', ['class' => 'btn btn-success clear-fullmap']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">

                            <div id="products">
                                <?=
                                GridView::widget([
                                    'dataProvider'   => $dataProvider,
                                    'id'             => 'fullmapGrid',
                                    'filterModel'    => $searchModel,
                                    'filterPosition' => false,
                                    'formatter'      => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                                    'summary'        => '',
                                    'pjax'           => true,
                                    'tableOptions'   => ['class' => 'table table-bordered table-striped dataTable'],
                                    'options'        => ['class' => 'table-responsive'],
                                    /* 'rowOptions'=>function($model) use ($cart){
                                        foreach ($cart as $vendor) {
                                            foreach ($vendor['items'] as $item) {
                                                if ($model['id'] == $item['id']) {
                                                    return ['class' => 'success'];
                                                }
                                            }
                                        }
                                    }, */
                                    'pager'          => [
                                        'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed
                                    ],
                                    'columns'        => [
                                        [
                                            // 'visible' => ($organization->type_id == Organization::TYPE_SUPPLIER) ? true : false,
                                            'class'           => 'yii\grid\CheckboxColumn',
                                            'contentOptions'  => ['class' => 'small_cell_checkbox'],
                                            'headerOptions'   => ['style' => 'text-align:center;'],
                                            'checkboxOptions' => function ($model, $key, $index, $widget) use ($selected) {
                                                return ['value' => $model['id'], 'class' => 'checkbox-export', 'checked' => (in_array($model['id'], $selected)) ? 'checked' : ""];
                                            }
                                        ],
                                        'id',
                                        [
                                            'format'    => 'raw',
                                            'attribute' => 'product',
                                            'width'     => '400px',
                                            'value'     => function ($data) {
                                                $note = ""; //empty($data['note']) ? "" : "<div><i>" . $data['note'] . "</i></div>";
                                                $productUrl = Html::a(Html::decode(Html::decode($data['product'])), Url::to(['/order/ajax-show-details', 'id' => $data['id'], 'cat_id' => $data['cat_id']]), [
                                                    'data'  => [
                                                        'target'   => '#showDetails',
                                                        'toggle'   => 'modal',
                                                        'backdrop' => 'static',
                                                    ],
                                                    'title' => Yii::t('message', 'frontend.views.order.details', ['ru' => 'Подробности']),
                                                ]);
//                                        $productUrl = "<a title = 'Подробности' data-target='#showDetails' data-toggle='modal' data-backdrop='static' href='".
//                                                Url::to(['order/ajax-show-details', 'id' => $data['id'], 'cat_id' => $data['cat_id']]).
//                                                "'>".$data['product']."</a>";
                                                return "<div class='grid-prod'>" . $productUrl . "</div>$note<div>" . Yii::t('message', 'frontend.views.order.vendor_two', ['ru' => 'Поставщик:']) . "  "
                                                    . $data['name'] . "</div><div class='grid-article'>" . Yii::t('message', 'frontend.views.order.art', ['ru' => 'Артикул:']) . "  <span>"
                                                    . $data['article'] . "</span></div>";
                                            },
                                            'label'     => Yii::t('message', 'frontend.views.order.product_name', ['ru' => 'Название продукта']),
                                        ],
                                        [
                                            'class'       => 'kartik\grid\EditableColumn',
                                            'attribute'   => 'pdenom',
                                            //       'value' => function ($model) {
                                            //       $model->pdenom = $model->product->denom;
                                            //       return $model->pdenom;
                                            //       },
                                            'label'       => 'Наименование SH',
                                            //  'pageSummary' => 'Total',
                                            'vAlign'      => 'middle',
                                            'width'       => '210px',
                                            'refreshGrid' => true,

                                            'editableOptions' => [
                                                'asPopover' => true,
                                                'name'      => 'pdenom',

                                                'formOptions' => ['action' => ['editpdenom']],
                                                'header'      => 'Продукт R-keeper',
                                                'size'        => 'md',
                                                'inputType'   => \kartik\editable\Editable::INPUT_SELECT2,
                                                //'widgetClass'=> 'kartik\datecontrol\DateControl',
                                                'options'     => [
                                                    //   'initValueText' => $productDesc,

                                                    //'data' => $pdenom,
                                                    //'data' => [1 =>1, 2=>2],

                                                    'options'       => ['placeholder' => 'Выберите продукт из списка',

                                                    ],
                                                    'pluginOptions' => [
                                                        'minimumInputLength' => 2,

                                                        'ajax'       => [
                                                            'url'      => Url::toRoute('autocomplete'),
                                                            'dataType' => 'json',
                                                            'data'     => new JsExpression('function(params) { return {term:params.term}; }')
                                                        ],
                                                        'allowClear' => true
                                                    ],
                                                    'pluginEvents'  => [
                                                        //"select2:select" => "function() { alert(1);}",
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
                                            'attribute' => 'unitname',
                                            'value'     => function ($model) {
                                                if (!empty($model['unitname'])) {

                                                    return $model['unitname'];
                                                }
                                                return 'Не задано';
                                            },
                                            'format'    => 'raw',
                                            'label'     => 'Ед.изм. SH',
                                        ],

                                        [
                                            'class'           => 'kartik\grid\EditableColumn',
                                            'attribute'       => 'koef',
                                            'refreshGrid'     => true,
                                            'editableOptions' => [
                                                'name'        => 'koef',
                                                'asPopover'   => true,
                                                'header'      => ':<br><strong>1 единица Mixcart равна:&nbsp; &nbsp;</srong>',
                                                'inputType'   => \kartik\editable\Editable::INPUT_TEXT,
                                                'formOptions' => [
                                                    'action'                 => Url::toRoute('editkoef'),
                                                    'enableClientValidation' => false,
                                                ],
                                            ],
                                            'hAlign'          => 'right',
                                            'vAlign'          => 'middle',
                                            // 'width'=>'100px',
                                            'format'          => ['decimal', 6],

                                            'pageSummary' => true
                                        ],
                                        [
                                            'class'       => 'kartik\grid\EditableColumn',
                                            'attribute'   => 'store',
                                            //       'value' => function ($model) {
                                            //       $model->pdenom = $model->product->denom;
                                            //       return $model->pdenom;
                                            //       },
                                            'label'       => 'Склад SH',
                                            //  'pageSummary' => 'Total',
                                            'vAlign'      => 'middle',
                                            'width'       => '210px',
                                            'refreshGrid' => true,

                                            'editableOptions' => [
                                                'asPopover'   => true,
                                                'name'        => 'store',
                                                'data'        => $stores,
                                                'formOptions' => ['action' => ['editstore']],
                                                'header'      => 'Склад SH',
                                                'size'        => 'md',
                                                'inputType'   => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                                                //'widgetClass'=> 'kartik\datecontrol\DateControl',
                                                'options'     => [
                                                    //   'initValueText' => $productDesc,

                                                    //'data' => $pdenom,
                                                    //'data' => [1 =>1, 2=>2],

                                                    'options' => ['placeholder' => 'Выберите склад из списка',

                                                    ],
                                                    /*   'pluginOptions' => [
                                                           'minimumInputLength' => 2,

                                                           'ajax' => [
                                                               'url' => Url::toRoute('autocomplete'),
                                                               'dataType' => 'json',
                                                               'data' => new JsExpression('function(params) { return {term:params.term}; }')
                                                           ],
                                                           'allowClear' => true
                                                       ],
                                                       'pluginEvents' => [
                                                           //"select2:select" => "function() { alert(1);}",
                                                           "select2:select" => "function() {
                                                                           if($(this).val() == 0)
                                                                           {
                                                                                $('#agent-modal').modal('show');
                                                                           }
                                                           }",
                                                       ] */

                                                ]
                                            ]],

                                        [
                                            'attribute' => 'vat',
                                            'value'     => function ($model) {
                                                if (isset($model['vat'])) {

                                                    return $model['vat'] / 100;
                                                }
                                                return 'Не задано';
                                            },
                                            'format'    => 'raw',
                                            'label'     => 'Ставка НДС',
                                        ],

                                        [
                                            'class'          => 'yii\grid\ActionColumn',
                                            'contentOptions' => ['style' => 'width: 6%;'],
                                            'template'       => '{zero}&nbsp;{ten}&nbsp;{eighteen}&nbsp;{twenty}',
                                            // 'header' => '<a class="label label-default" href="setvatz">0</a><a class="label label-default" href="setvatt">10</a><a class="label label-default" href="setvate">18</a><a class="label label-default" href="setvate">20</a>',
                                            //  'header' => '<span align="center"> <button id="btnZero" type="button" onClick="location.href=\''.$sLinkzero.'\';" class="btn btn-xs btn-link" style="color:green;">0</button>'.
                                            //      '<button id="btnTen" type="button" onClick="location.href=\''.$sLinkten.'\';" class="btn btn-xs btn-link" style="color:green;">10</button>'.
                                            //      '<button id="btnEight" type="button" onClick="location.href=\''.$sLinkeight.'\';" class="btn btn-xs btn-link" style="color:green;">18</button></span>',
                                            //      '<button id="btnEight" type="button" onClick="location.href=\''.$sLinktwenty.'\';" class="btn btn-xs btn-link" style="color:green;">20</button></span>',

                                            //  'sort' => false,
                                            //  '' => false,

                                            'visibleButtons' => [
                                                'zero' => function ($model) {
                                                    /*    if (!empty($model['pdenom']))
                                                            return true;
                                                        else
                                                            return false; */
                                                    return true;
                                                },
                                            ],
                                            'buttons'        => [
                                                'zero'     => function ($model, $data, $index) {

                                                    if ($data['vat'] == 0) {
                                                        $tClass = "label label-success";
                                                        $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";

                                                    } else {
                                                        $tClass = "label label-default";
                                                        $tStyle = "";
                                                    }

                                                    //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                    $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/fullmap/chvat', 'id' => $data['id'], 'vat' => 0]);
                                                    return \yii\helpers\Html::a('&nbsp;0', $customurl,
                                                        ['title' => Yii::t('backend', '0%'), 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle]);

                                                },
                                                'ten'      => function ($model, $data, $index) {

                                                    if ($data['vat'] == 1000) {
                                                        $tClass = "label label-success";
                                                        $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                    } else {
                                                        $tClass = "label label-default";
                                                        $tStyle = "";
                                                    }

                                                    //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                    $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/fullmap/chvat', 'id' => $data['id'], 'vat' => '1000']);
                                                    return \yii\helpers\Html::a('10', $customurl,
                                                        ['title' => Yii::t('backend', '10%'), 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle]);
                                                },
                                                'eighteen' => function ($model, $data, $index) {

                                                    if ($data['vat'] == 1800) {
                                                        $tClass = "label label-success";
                                                        $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                    } else {
                                                        $tClass = "label label-default";
                                                        $tStyle = "";
                                                    }

                                                    //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                    $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/fullmap/chvat', 'id' => $data['id'], 'vat' => '1800']);
                                                    return \yii\helpers\Html::a('18', $customurl,
                                                        ['title' => Yii::t('backend', '18%'), 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle]);
                                                },
                                                'twenty'   => function ($model, $data, $index) {

                                                    if ($data['vat'] == 2000) {
                                                        $tClass = "label label-success";
                                                        $tStyle = "pointer-events: none; cursor: default; text-decoration: none;";
                                                    } else {
                                                        $tClass = "label label-default";
                                                        $tStyle = "";
                                                    }

                                                    //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                    $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/fullmap/chvat', 'id' => $data['id'], 'vat' => '2000']);
                                                    return \yii\helpers\Html::a('20', $customurl,
                                                        ['title' => Yii::t('backend', '20%'), 'data-pjax' => "0", 'class' => $tClass, 'style' => $tStyle]);
                                                },

                                            ]
                                        ],

                                        /*    [
                                                'format' => 'raw',
                                                'attribute' => 'price',
                                                'value' => function ($data) {
                                                    $unit = empty($data['ed']) ? '' : " / " . Yii::t('app', $data['ed']);
                                                    return '<span data-toggle="tooltip" data-placement="bottom" title="'.Yii::t('message', 'frontend.views.order.price_update', ['ru'=>'Обновлена:']).' '.Yii::$app->formatter->asDatetime($data['updated_at'], "dd-MM-YY").'"><b>'
                                                        . $data['price'] . '</b> ' . $data['symbol'] . $unit.'</span>';
                                                },
                                                'label' => Yii::t('message', 'frontend.views.order.price', ['ru'=>'Цена']),
                                                'contentOptions' => ['class' => 'width150'],
                                                'headerOptions' => ['class' => 'width150']
                                            ],
                                        */

                                        /*    [
                                                'attribute' => 'units',
                                                'value' => 'units',
                                                'label' => Yii::t('message', 'frontend.views.order.freq', ['ru'=>'Кратность']),
                                            ],
                                        */

                                        /*    [
                                                'format' => 'raw',
                                                'value' => function ($data) {
                                                    return TouchSpin::widget([
                                                        'name' => '',
                                                        'pluginOptions' => [
                                                            'initval' => 0.100,
                                                            'min' => (isset($data['units']) && ($data['units'] > 0)) ? $data['units'] : 0,
                                                            'max' => PHP_INT_MAX,
                                                            'step' => (isset($data['units']) && ($data['units'])) ? $data['units'] : 1,
                                                            'decimals' => (empty($data["units"]) || (fmod($data["units"], 1) > 0)) ? 3 : 0,
                                                            'forcestepdivisibility' => (isset($data['units']) && $data['units'] && (floor($data['units']) == $data['units'])) ? 'floor' : 'none',
                                                            'buttonup_class' => 'btn btn-default',
                                                            'buttondown_class' => 'btn btn-default',
                                                            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                                            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                                                        ],
                                                        'options' => ['class' => 'quantity form-control '],
                                                    ]);
                                                },
                                                'label' => Yii::t('message', 'frontend.views.order.amount', ['ru'=>'Количество']),
                                                'contentOptions' => ['class' => 'width150'],
                                                'headerOptions' => ['class' => 'width150']
                                            ],
                                        */
                                        //'note',
                                        /*
                                        [
                                            'format' => 'raw',
                                            'value' => function ($data) {
                                                $btnNote = Html::a('<i class="fa fa-comment m-r-xs"></i>', '#', [
                                                    'class' => 'add-note btn btn-default margin-right-5',
                                                    'data' => [
                                                        'id' => $data['id'],
                                                        'cat' => $data['cat_id'],
                                                        'toggle' => 'tooltip',
                                                        'original-title' => Yii::t('message', 'frontend.views.order.add_check', ['ru'=>'Добавить заметку к товару']),
                                                        'target' => "#changeQuantity",
                                                        'toggle' => "modal",
                                                        'backdrop' => "static",
                                                    ],
                                                ]);
                                                $btnAdd = Html::a('<i class="fa fa-shopping-cart m-r-xs"></i>', '#', [
                                                    'class' => 'add-to-cart btn btn-outline-success',
                                                    'data-id' => $data['id'],
                                                    'data-cat' => $data['cat_id'],
                                                    'title' => Yii::t('message', 'frontend.views.order.add_to_basket', ['ru'=>'Добавить в корзину']),
                                                ]);
                                                return $btnAdd;
                                            },
                                            'contentOptions' => ['class' => 'text-center'],
                                            'headerOptions' => ['style' => 'width: 50px;']
                                        ],
                                        */
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
