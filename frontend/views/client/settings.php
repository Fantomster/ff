<?php
use yii\bootstrap\Tabs;

$this->registerJs(
        '$("document").ready(function(){
            $(".settings").on("click", "#cancelOrg", function() {
                alert("cancel");
            });
            $(".settings").on("click", "#saveOrg", function() {
                alert("save");
            });
            $(".settings").on("change paste keyup", ".org-info>.col-lg-5>input[text]", function() {
                $("#saveOrg").prop( "disabled", true );
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
        [
            'label' => 'Бюджет',
            'content' => $this->render('settings/_budget'),
        ],
    ],
]) ?>
</div>
