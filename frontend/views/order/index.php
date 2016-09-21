<?php
use yii\helpers\Url;

$this->registerJs("

    $('td').click(function (e) {
        var id = $(this).closest('tr').data('id');
        if(e.target == this)
            location.href = '" . Url::to(['order/view']) . "&id=' + id;
    });

");
$this->registerCss("
    tr:hover{cursor: pointer;}
        ");
echo $this->render('_history', compact('searchModel', 'dataProvider'));