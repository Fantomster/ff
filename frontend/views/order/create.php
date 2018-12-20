<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\grid\GridView;
use kartik\form\ActiveForm;
use yii\widgets\Breadcrumbs;
use kartik\widgets\TouchSpin;
use yii\web\View;

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
        '$(document).ready(function(){
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
            
            var ajax_id = null;
            
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
                   
                   if(ajax_id !== null) {
                        ajax_id.abort();
                   }
                   
                   ajax_id = $.ajax({
                      type: "POST",
                      url: "' . Url::to(['/order/ajax-add-to-cart-notice']) . '",
                      data: {},
                      async: true
                   });
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
        });'
);
?>
<img id="cart-image" src="/images/cart.png" style="position:absolute;left:-100%;">
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#"><?= Yii::t('message', 'frontend.views.order.all_goods_two', ['ru' => 'Все продукты']) ?></a></li>
            <li>
                <a href="<?= Url::to(['order/guides']) ?>">
                    <?= Yii::t('message', 'frontend.views.order.orders_guides_two', ['ru' => 'Шаблоны заказов']) ?>
                    <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="<?= Url::to(['order/favorites']) ?>">
                    <?= Yii::t('message', 'frontend.views.order.freq_goods_two', ['ru' => 'Часто заказываемые товары']) ?>
                    <small class="label bg-yellow">new</small>
                </a>
            </li>
            <?php if ($client->parent_id == null && Yii::$app->user->identity->role_id != \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR) : ?>
            <li>
                <a href="<?= Url::to(['order/product-filter']) ?>">
                    <?= Yii::t('message', 'frontend.views.order.filter_product', ['ru' => 'Фильтрация товаров']) ?>
                    <small class="label bg-yellow">new</small>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <div class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="guid-header">
                        <?php
                        $form = ActiveForm::begin([
                                    'options' => [
                                        'id' => 'searchForm',
                                        'class' => "navbar-form no-padding no-margin",
                                        'role' => 'search',
                                    ],
                        ]);
                        ?>
                        <?=
                                $form->field($searchModel, 'searchString', [
                                    'addon' => [
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
                                    'id' => 'searchString',
                                    'class' => 'form-control',
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
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php
                    Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'createOrder', 'timeout' => 5000]);
                    ?>
                    <div id="products">
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'filterPosition' => false,
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                            'summary' => '',
                            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
                            'options' => ['class' => 'table-responsive'],
                            'rowOptions' => function ($model) use ($cartItems) {
                                if (in_array($model['id'], $cartItems)) {
                                    return ['class' => 'success'];
                                }
                            },
                            'pager' => [
                                'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed            
                            ],
                            'columns' => [
                                [
                                    'format' => 'raw',
                                    'attribute' => 'product',
                                    'value' => function ($data) {
                                        $note = ""; //empty($data['note']) ? "" : "<div><i>" . $data['note'] . "</i></div>";
                                        $productUrl = Html::a(Html::decode(Html::decode($data['product'])), Url::to(['order/ajax-show-details', 'id' => $data['id'], 'cat_id' => $data['cat_id']]), [
                                                    'data' => [
                                                        'target' => '#showDetails',
                                                        'toggle' => 'modal',
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
                                    'label' => Yii::t('message', 'frontend.views.order.product_name', ['ru' => 'Название продукта']),
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'price',
                                    'value' => function ($data) {
                                        if ($data['price']) {
                                            $price = $data['price'];
                                        } else {
                                            $catBaseGood = \common\models\CatalogBaseGoods::findOne(['id' => $data['id']]);
                                            $price = $catBaseGood->price;
                                        }
                                        $unit = empty($data['ed']) ? '' : " / " . Yii::t('app', $data['ed']);
                                        return '<span data-toggle="tooltip" data-placement="bottom" title="' . Yii::t('message', 'frontend.views.order.price_update', ['ru' => 'Обновлена:']) . ' ' . Yii::$app->formatter->asDatetime($data['updated_at'], "dd-MM-YY") . '"><b>'
                                                . $price . '</b> ' . $data['symbol'] . $unit . '</span>';
                                    },
                                    'label' => Yii::t('message', 'frontend.views.order.price', ['ru' => 'Цена']),
                                    'contentOptions' => ['class' => 'width150'],
                                    'headerOptions' => ['class' => 'width150']
                                ],
                                [
                                    'attribute' => 'units',
                                    'value' => 'units',
                                    'label' => Yii::t('message', 'frontend.views.order.freq', ['ru' => 'Кратность']),
                                ],
                                [
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return TouchSpin::widget([
                                                    'name' => '',
                                                    'pluginOptions' => [
                                                        'initval' => 0.100,
                                                        'min' => (isset($data['units']) && ($data['units'] > 0)) ? $data['units'] : 0,
                                                        'max' => PHP_INT_MAX,
                                                        'step' => (isset($data['units']) && ($data['units'])) ? $data['units'] : 0.1,
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
                                    'label' => Yii::t('message', 'frontend.views.order.amount', ['ru' => 'Количество']),
                                    'contentOptions' => ['class' => 'width150'],
                                    'headerOptions' => ['class' => 'width150']
                                ],
                                //'note',
                                [
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        $btnNote = Html::a('<i class="fa fa-comment m-r-xs"></i>', '#', [
                                                    'class' => 'add-note btn btn-default margin-right-5',
                                                    'data' => [
                                                        'id' => $data['id'],
                                                        'cat' => $data['cat_id'],
                                                        'toggle' => 'tooltip',
                                                        'original-title' => Yii::t('message', 'frontend.views.order.add_check', ['ru' => 'Добавить заметку к товару']),
                                                        'target' => "#changeQuantity",
                                                        'toggle' => "modal",
                                                        'backdrop' => "static",
                                                    ],
                                        ]);
                                        $btnAdd = Html::a('<i class="fa fa-shopping-cart m-r-xs"></i>', '#', [
                                                    'class' => 'add-to-cart btn btn-outline-success',
                                                    'data-id' => $data['id'],
                                                    'data-cat' => $data['cat_id'],
                                                    'title' => Yii::t('message', 'frontend.views.order.add_to_basket', ['ru' => 'Добавить в корзину']),
                                        ]);
                                        return $btnAdd;
                                    },
                                    'contentOptions' => ['class' => 'text-center'],
                                    'headerOptions' => ['style' => 'width: 50px;']
                                ],
                            ],
                        ])
                        ?>
                    </div>
<?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?=
Modal::widget([
    'id' => 'changeQuantity',
    'clientOptions' => false,
])
?>
<?=
Modal::widget([
    'id' => 'addNote',
    'clientOptions' => false,
])
?>
<?=
Modal::widget([
    'id' => 'showDetails',
    'clientOptions' => false,
    'size' => 'modal-lg',
])
?>
