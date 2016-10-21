<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\grid\GridView;
use kartik\form\ActiveForm;
use yii\widgets\Breadcrumbs;

$this->registerJs(
        '$("document").ready(function(){
            var timer = null;
            $("#createP").on("change", "#selectedCategory", function(e) {
                var form = $("#createForm");
                form.submit();
            });
            $("#createP").on("change", "#selectedVendor", function(e) {
                var form = $("#createForm");
                form.submit();
            });
            $("#createP").on("click", ".add-to-cart", function(e) {
                e.preventDefault();
                $("#loader-show").showLoading();
                quantity = $(this).parent().parent().find(".quantity").val();
                $.post(
                    "' . Url::to(['/order/ajax-add-to-cart']) . '",
                    {"id": $(this).data("id"), "quantity": quantity, "cat_id": $(this).data("cat")}
                ).done(function(result) {
                    if (result) {
                        $.pjax.reload({container: "#cart"});
                    }
                    $("#loader-show").hideLoading();
                });
            });
            $("#createP").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#createForm").submit();
                }, 300);
            });
            $("#createOrder").on("pjax:complete", function() {
                var searchInput = $("#searchString");
                var strLength = searchInput.val().length * 2;
                searchInput.focus();
                searchInput[0].setSelectionRange(strLength, strLength);
            });
            $("#orders").on("click", ".btn-danger", function(e) {
                $("#loader-show").showLoading();
                $.post(
                    "' . Url::to(['/order/ajax-remove-position']) . '",
                    {vendor_id: $(this).parent().data("vendor_id"), product_id: $(this).parent().data("product_id")}
                )
                .done(function (result) {
                    if (result) {
                        $.pjax.reload({container: "#cart"});
                    }
                    $("#loader-show").hideLoading();
                });
                return false;
            });
            $("body").on("hidden.bs.modal", "#changeQuantity", function() {
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
                    if (result) {
                        $.pjax.reload({container: "#cart"});
                    }
                    $("#loader-show").hideLoading();
                });
            });
        });'
);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-opencart"></i> Разместить заказ
        <small>Создание заказов из списка доступных товаров</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Разместить заказ',
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-9">
            <div class="box box-info" id="createP">
                <div class="box-header with-border">
                    <h3 class="box-title">Список товаров</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">    
                    <?php
                    Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'createOrder', 'timeout' => 3000]);
                    $form = ActiveForm::begin([
                                'options' => [
                                    'data-pjax' => true,
                                    'id' => 'createForm',
                                    'class' => "navbar-form no-padding no-margin",
                                    'role' => 'search',
                                ],
                                'method' => 'get',
                    ]);
                    ?>
                    <div>
                        <?=
                                $form->field($searchModel, 'searchString', [
                                    'addon' => [
                                        'prepend' => [
                                            'content' => '<i class="fa fa-search"></i>',
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
                                $form->field($searchModel, 'selectedCategory')
                                ->dropDownList($client->getRestaurantCategories(), [
                                    'id' => 'selectedCategory',
                                    'class' => 'form-control margin-right-15'])
                                ->label(false)
                        ?>
                        <?=
                                $form->field($searchModel, 'selectedVendor')
                                ->dropDownList($vendors, ['id' => 'selectedVendor'])
                                ->label(false)
                        ?>
                        <?php ActiveForm::end(); ?>
                    </div>
                    <div style="padding-right: 10px; float: left; width: 100%" id="products">
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'filterPosition' => false,
                            'summary' => '',
                            //'tableOptions' => ['class' => 'table no-margin table-hover'],
                            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
                            'options' => ['class' => 'table-responsive'],
                            'columns' => [
                                [
                                    'format' => 'raw',
                                    'attribute' => 'product',
                                    'value' => function($data) {
                                        return "<div class='grid-prod'>" . $data['product'] . "</div><div>Поставщик: "
                                                . $data['name'] . "</div><div class='grid-article'>Артикул: <span>"
                                                . $data['article'] . "</span></div>";
                                    },
                                    'label' => 'Название продукта',
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'price',
                                    'value' => function($data) {
                                        return '<b>'.$data['price'] . '</b> <i class="fa fa-fw fa-rub"></i> / ' . $data['units'];
                                    },
                                    'label' => 'Цена',
                                    'contentOptions' => ['class' => 'width150'],
                                    'headerOptions' => ['class' => 'width150']
                                ],
                                [
                                    'format' => 'raw',
                                    'value' => function($data) {
                                        return Html::textInput('', 1, ['class' => 'quantity form-control']);
                                    },
                                            'label' => 'Количество',
                                            'contentOptions' => ['class' => 'width150'],
                                            'headerOptions' => ['class' => 'width150']
                                        ],
                                        //'note',
                                        [
                                            'format' => 'raw',
                                            'value' => function ($data) {
                                                $link = Html::a('<i class="fa fa-shopping-cart m-r-xs"></i> Добавить', '#', [
                                                            'class' => 'add-to-cart btn btn-success',
                                                            'data-id' => $data['id'],
                                                            'data-cat' => $data['cat_id'],
                                                ]);
                                                return $link;
                                            },
                                                    'contentOptions' => ['class' => 'width150 text-center'],
                                                    'headerOptions' => ['class' => 'width150']
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
                        <div class="col-md-3">
                            <div class="box box-info ">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Корзина</h3>
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body" id="orders">
                                    <?= $this->render('_cart', compact('orders')) ?>
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
