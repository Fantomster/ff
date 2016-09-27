<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;

$this->registerJs(
        '$("document").ready(function(){
            $(".category").on("click", function(e) {
                e.preventDefault();
                if ($(this).data("selected") == 0) {
                    $(this).removeClass("btn-default").addClass("btn-primary");
                    $(this).data("selected", 1);
                } else {
                    $(this).removeClass("btn-primary").addClass("btn-default");
                    $(this).data("selected", 0);
                }
                $.post(
                    "' . Url::to(['/order/ajax-categories']) . '",
                    {"id": $(this).data("id"), "selected": $(this).data("selected")}
                ).done(function(result) {
                    $("#vendors").html(result);
                    $.pjax.reload({container: "#productsList"});
                });
            });
            $("#vendors").on("click", ".vendor", function(e) {
                e.preventDefault();
                if ($(this).data("selected") == 0) {
                    $(this).addClass("active");
                    $(this).data("selected", 1);
                } else {
                    $(this).removeClass("active");
                    $(this).data("selected", 0);
                }
                $.post(
                    "' . Url::to(['/order/ajax-vendors']) . '",
                    {"id": $(this).data("id"), "selected": $(this).data("selected")}
                ).done(function(result) {
                    $.pjax.reload({container: "#productsList"});
                });
            });
            $("#products").on("click", ".add-to-cart", function(e) {
                e.preventDefault();
                $.post(
                    "' . Url::to(['/order/ajax-add-to-cart']) . '",
                    {"id": $(this).data("id")}
                ).done(function(result) {
                    $("#orders").html(result);
                });
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
 <div class="panel panel-primary">
      <div class="panel-heading">Категории</div>
      <div class="panel-body">
          <?php
foreach ($categories as $cat) {
    echo Html::button(
            $cat['name'], [
        'class' => $cat['selected'] ? 'btn btn-primary category' : 'btn btn-default category',
        'data-id' => $cat['id'],
        'data-name' => $cat['name'],
        'data-selected' => $cat['selected'],
    ]);
}
          ?>
      </div>
    </div>
<div style="padding-top: 20px;">
    <div class="list-group" style="padding-right: 10px; width: 300px; float: left;" id="vendors">
        <?= $this->render('_vendors', compact('vendors')) ?>
    </div>
    <div style="padding-right: 10px; float: left; width: 60%" id="products">
        <?= $this->render('_products', compact('searchModel', 'dataProvider')) ?>
    </div>
    <div style="float: left;" id="orders">
        <?= $this->render('_orders', compact('orders')) ?>
    </div>
</div>

<?=
Modal::widget([
    'id' => 'showOrder',
    'clientOptions' => false,
])
?>