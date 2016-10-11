<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\grid\GridView;
use kartik\widgets\Select2;
use yii\widgets\ActiveForm;

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
                quantity = $(this).parent().parent().find(".quantity").val();
                $.post(
                    "' . Url::to(['/order/ajax-add-to-cart']) . '",
                    {"id": $(this).data("id"), "quantity": quantity, "cat_id": $(this).data("cat")}
                ).done(function(result) {
                    $("#orders").html(result);
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
            $("body").on("hidden.bs.modal", "#showOrder", function() {
                $(this).data("bs.modal", null);
                $.post(
                    "' . Url::to(['/order/ajax-order-refresh']) . '"
                ).done(function(result) {
                    $("#orders").html(result);
                });
            });
            $("#showOrder").on("click", ".sendOrder", function() {
                var form = $("#order-form");
                $.post(
                    "' . Url::to(['/order/ajax-make-order']) . '",
                    form.serialize()
                )
                .done(function(result) {
                    form.replaceWith(result);
                });
                return false;
            });
            $("#showOrder").on("click", ".saveOrder", function() {
                var form = $("#order-form");
                $.post(
                    "' . Url::to(['/order/ajax-modify-cart']) . '",
                    form.serialize()
                )
                .done(function(result) {
                    form.replaceWith(result);
                });
                return false;
            });
            $("#showOrder").on("click", ".clearOrder", function() {
                var form = $("#order-form");
                $.post(
                    "' . Url::to(['/order/ajax-clear-order']) . '",
                    form.serialize()
                )
                .done(function(result) {
                    form.replaceWith(result);
                });
                return false;
            });
            $("#orders").on("click", ".btn-danger", function() {
                $.post(
                    "' . Url::to(['/order/ajax-remove-position']) . '",
                    {vendor_id: $(this).data("vendor_id"), product_id: $(this).data("product_id")}
                )
                .done(function (result) {
                    $("#orders").html(result);
                });
            });
        });'
);
?>
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
                                'class' => "navbar-form",
                                'role' => 'search',
                            ],
                            'method' => 'get',
                ]);
                ?>
                <div>
                    <?=
                            $form->field($searchModel, 'searchString')
                            ->textInput([
                                'id' => 'searchString',
                                'class' => 'form-control',
                                'placeholder' => 'Поиск'])
                            ->label(false)
                    ?>
                    <?=
                            $form->field($searchModel, 'selectedCategory')
                            ->dropDownList($client->getRestaurantCategories(), ['id' => 'selectedCategory'])
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
                        'columns' => [
                            [
                                'format' => 'raw',
                                'attribute' => 'product',
                                'value' => function($data) {
                                    return "<div class='grid-prod'>" . $data['product'] . "</div><div>"
                                            . $data['name'] . "</div><div class='grid-article'>артикул: "
                                            . $data['article'] . "</div>";
                                },
                                'label' => 'Название продукта',
                            ],
                            [
                                'attribute' => 'price',
                                'value' => function($data) {
                                    return $data['price'] . ' руб / ' . $data['units'];
                                },
                                'label' => 'Цена'
                            ],
                            [
                                'format' => 'raw',
                                'value' => function($data) {
                                    return Html::textInput('', 1, ['class' => 'quantity']);
                                },
                                        'label' => 'Количество'
                                    ],
                                    //'note',
                                    [
                                        'format' => 'raw',
                                        'value' => function ($data) {
                                            $link = Html::a('<span class="glyphicon glyphicon-plus"></span>', '#', [
                                                        'class' => 'add-to-cart',
                                                        'data-id' => $data['id'],
                                                        'data-cat' => $data['cat_id'],
                                            ]);
                                            return $link;
                                        },
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
                <?= $this->render('_orders', compact('orders')) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?=
                Modal::widget([
                    'id' => 'showOrder',
                    'clientOptions' => false,
                ])
                ?>
