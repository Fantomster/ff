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
        });'
);
?>
<div id="createP">
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
    <div style="padding-top: 20px; float: left; width: 60%;">
        <div id="categories">
            <?=
            ''
//            Select2::widget([
//                'name' => 'selectedCategory',
//                'value' => $selectedCategory,
//                'id' => 'selectedCategory',
//                'data' => $client->getRestaurantCategories(),
//                'options' => ['placeholder' => 'Все категории'],
//                'pluginOptions' => [
//                    'allowClear' => true
//                ],
//            ])
            ?>
        </div>
        <div id="vendors">
            <?=
            ''
//            Select2::widget([
//                'name' => 'selectedVendor',
//                'value' => $selectedVendor,
//                'id' => 'selectedVendor',
//                'data' => $vendors,
//                'options' => ['placeholder' => 'Все поставщики'],
//                'pluginOptions' => [
//                    'allowClear' => true
//                ],
//            ])
            ?>
        </div>
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
                    ->widget(Select2::classname(), [
                        'data' => $client->getRestaurantCategories(),
                        'options' => ['placeholder' => 'Все категории', 'id' => 'selectedCategory',],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label(false);
            ?>
            <?=
                    $form->field($searchModel, 'selectedVendor')
                    ->widget(Select2::classname(), [
                        'data' => $vendors,
                        'options' => ['placeholder' => 'Все поставщики', 'id' => 'selectedVendor',],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label(false);
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
                    </div>
                    <div style="float: left;" id="orders">
                        <?= $this->render('_orders', compact('orders')) ?>
                    </div>
                </form>
                <?php
                Pjax::end();
                ?>
                </div>
                <?=
                Modal::widget([
                    'id' => 'showOrder',
                    'clientOptions' => false,
                ])
                ?>