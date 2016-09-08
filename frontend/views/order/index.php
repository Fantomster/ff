<?php
use yii\helpers\Html;

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
            });
        });'
);

//var_dump($categories);
foreach($categories as $cat) { 
    echo Html::button(
            $cat['name'], 
            [
                'class' => $cat['selected'] ? 'btn btn-primary category' : 'btn btn-default category', 
                'data-id' => $cat['id'], 
                'data-name' => $cat['name'],
                'data-selected' => $cat['selected'],
            ]);
}
?>
<div style="padding-top: 20px;">
    <div class="list-group" style="padding-right: 10px; width: 300px; float: left;" id="vendors">
        <?= $this->render('_vendors', compact('vendors'))?>
    </div>
    <div style="padding-right: 10px; float: left;" id="products">
        <?= $this->render('_products', compact('dataProvider'))?>
    </div>
    <div style="float: left;" id="orders">
        <?= $this->render('_orders')?>
    </div>
</div>