<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\grid\GridView;
use kartik\form\ActiveForm;
use yii\widgets\Breadcrumbs;
use kartik\widgets\TouchSpin;

$this->title = 'Разместить заказ';

yii\jui\JuiAsset::register($this);

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
            $(document).on("click", ".add-to-cart", function(e) {
                e.preventDefault();
                //$("#loader-show").showLoading();
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
                    //$("#loader-show").hideLoading();
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
            $("#orders").on("click", ".delete-position", function(e) {
                $("#loader-show").showLoading();
                clicked = $(this);
                $.post(
                    clicked.data("url")
                )
                .done(function (result) {
                    $("#loader-show").hideLoading();
                });
                return false;
            });
            $("body").on("hidden.bs.modal", "#changeQuantity, #showDetails", function() {
                $(this).data("bs.modal", null);
            });
            $("body").on("submit", "#quantityForm", function() {
                return false;
            });
            $("#changeQuantity").on("click", ".save", function() {
                $("#loader-show").showLoading();
                var form = $("#quantityForm");
                $.post(
                    form.attr("action"),
                    form.serialize()
                )
                .done(function (result) {
                    $("#loader-show").hideLoading();
                });
            });
            $(document).on("click", ".pagination li a", function() {
                clearTimeout(timer);
                return true;
            });
        });'
);
?>
<img id="cart-image" src="/images/cart.png" style="position:absolute;left:-100%;">
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#">Все продукты</a></li>
            <li>
                <a href="<?= Url::to(['order/guides']) ?>">
                    Гиды заказов <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="<?= Url::to(['order/favorites']) ?>">
                    Фавориты <small class="label bg-yellow">new</small>
                </a>
            </li>
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
                                    'placeholder' => 'Поиск'])
                                ->label(false)
                        ?>
                        <?=
                                $form->field($searchModel, 'selectedVendor')
                                ->dropDownList($vendors, ['id' => 'selectedVendor'])
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
                            'pager' => [
                                'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed            
                            ],
                            'columns' => [
                                [
                                    'format' => 'raw',
                                    'attribute' => 'product',
                                    'value' => function($data) {
                                        $note = ""; //empty($data['note']) ? "" : "<div><i>" . $data['note'] . "</i></div>";
                                        $productUrl = Html::a($data['product'], Url::to(['order/ajax-show-details', 'id' => $data['id'], 'cat_id' => $data['cat_id']]), [
                                                    'data' => [
                                                        'target' => '#showDetails',
                                                        'toggle' => 'modal',
                                                        'backdrop' => 'static',
                                                    ],
                                                    'title' => 'Подробности',
                                        ]);
                                        return "<div class='grid-prod'>" . $productUrl . "</div>$note<div>Поставщик: "
                                                . $data['name'] . "</div><div class='grid-article'>Артикул: <span>"
                                                . $data['article'] . "</span></div>";
                                    },
                                    'label' => 'Название продукта',
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'price',
                                    'value' => function($data) {
                                        $unit = empty($data['ed']) ? '' : " / " . $data['ed'];
                                        return '<b>' . $data['price'] . '</b> <i class="fa fa-fw fa-rub"></i>' . $unit;
                                    },
                                    'label' => 'Цена',
                                    'contentOptions' => ['class' => 'width150'],
                                    'headerOptions' => ['class' => 'width150']
                                ],
                                [
                                    'attribute' => 'units',
                                    'value' => 'units',
                                    'label' => 'Кратность',
                                ],
                                [
                                    'format' => 'raw',
                                    'value' => function($data) {
                                        return TouchSpin::widget([
                                                    'name' => '',
                                                    'pluginOptions' => [
                                                        'initval' => 0.100,
                                                        'min' => (isset($data['units']) && ($data['units'] > 0)) ? $data['units'] : 0.001,
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
                                    'label' => 'Количество',
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
                                                        'original-title' => 'Добавить заметку к товару',
                                                        'target' => "#changeQuantity",
                                                        'toggle' => "modal",
                                                        'backdrop' => "static",
                                                    ],
                                        ]);
                                        $btnAdd = Html::a('<i class="fa fa-shopping-cart m-r-xs"></i>', '#', [
                                                    'class' => 'add-to-cart btn btn-outline-success',
                                                    'data-id' => $data['id'],
                                                    'data-cat' => $data['cat_id'],
                                                    'title' => 'Добавить в корзину',
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
                    </form>
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
