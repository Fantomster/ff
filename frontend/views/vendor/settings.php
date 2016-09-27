<?php
use yii\bootstrap\Tabs;
$this->registerJs(
        '$("document").ready(function(){
            $(".settings").on("click", "#cancelOrg", function() {
                var form = $("#generalSettings");
                $.get(
                    form.attr("action")
                )
                .done(function(result) {
                    form.replaceWith(result);
                });                
            });
            $(".settings").on("click", "#saveOrg", function() {
                var form = $("#generalSettings");
                $.post(
                    form.attr("action"),
                    form.serialize()
                )
                .done(function(result) {
                    form.replaceWith(result);
                });
            });
            $(".settings").on("change paste keyup", ".form-control", function() {
                $("#cancelOrg").prop( "disabled", false );
                $("#saveOrg").prop( "disabled", false );
            });
        });'
);
?>

<div class="settings">
<?= Tabs::widget([
    'items' => [
        [
            'label' => 'Общие',
            'content' => $this->render('settings/_info', compact('organization')),
            'active' => true,
        ],
        [
            'label' => 'Пользователи',
            'content' => $this->render('settings/_users', compact('dataProvider', 'searchModel')),
        ],
//        [
//            'label' => 'Доставка',
//            'content' => $this->render('settings/_delivery'),
//        ],
    ],
]) ?>
</div>