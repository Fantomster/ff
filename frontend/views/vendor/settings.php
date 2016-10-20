<?php
use yii\bootstrap\Tabs;
$this->registerJs(
        '$("document").ready(function(){
            $("#info").on("click", "#cancelOrg", function() {
                var form = $("#generalSettings");
                $.get(
                    form.attr("action")
                )
                .done(function(result) {
                    form.replaceWith(result);
                });                
            });
            $("#info").on("click", "#saveOrg", function() {
                var form = $("#generalSettings");
                $.post(
                    form.attr("action"),
                    form.serialize()
                )
                .done(function(result) {
                    form.replaceWith(result);
                });
            });
            $("#delivery").on("click", "#cancelDlv", function() {
                var form = $("#deliverySettings");
                $.get(
                    form.attr("action")
                )
                .done(function(result) {
                    $("#delivery").html(result);
                });                
            });
            $("#delivery").on("click", "#saveDlv", function() {
                var form = $("#deliverySettings");
                $.post(
                    form.attr("action"),
                    form.serialize()
                )
                .done(function(result) {
                    $("#delivery").html(result);
                });
            });
            $("#info").on("change paste keyup", ".form-control", function() {
                $("#cancelOrg").prop( "disabled", false );
                $("#saveOrg").prop( "disabled", false );
            });
            $("#delivery").on("change paste keyup", "input", function() {
                $("#cancelDlv").prop( "disabled", false );
                $("#saveDlv").prop( "disabled", false );
            });
        });'
);
?>
<div class="nav-tabs-custom settings">
<?= Tabs::widget([
    'items' => [
//        [
//            'label' => 'Общие',
//            'content' => $this->render('settings/_info', compact('organization')),
//            'active' => true,
//            'options' => ['id' => 'info'],
//        ],
        [
            'label' => 'Пользователи',
            'content' => $this->render('settings/_users', compact('dataProvider', 'searchModel')),
        ],
//        [
//            'label' => 'Доставка',
//            'content' => $this->render('settings/_delivery', compact('delivery')),
//            'options' => ['id' => 'delivery'],
//        ],
    ],
]) ?>
</div>