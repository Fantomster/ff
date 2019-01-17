<?php

use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;
use yii\web\View;

?>
<div id="loader-show"></div>
<div class="content-wrapper">
    <?= $content ?>
</div>
<footer class="main-footer">
    <div class="pull-right hidden-xs" style="width: 420px">
        <b><a href="mailto:info@mixcart.ru" target="_blank"><span class="fa fa-envelope"></span> info@mixcart.ru</a></b>&nbsp;&nbsp;&nbsp;&nbsp;
        <b><a href="tel:8-499-404-10-18" target="_blank"><span class="glyphicon glyphicon-phone"></span> 8-499-404-10-18</a></b>
        <p style="font-size: 11px;color:grey;display: inline-block;position: absolute; padding-left: 10px; padding-top: 3px; color: #999">
            <?= sprintf('Generation time: %0.2f', Yii::getLogger()->getElapsedTime()) ?>
        </p>
    </div>
    <strong>© 2016 - <?= date('Y') ?> MixCart</strong>
</footer>
<?php

$js = "
            $(document).snowfall({
                flakeCount: 30, // Количество снежинок
                flakeColor: '#ffffff', // Цвет снежинок если нет картинки
                flakeIndex: 999999, // z-index снежинок
                minSize: 8, // Минимальный размер снежинки
                maxSize: 12, // Максимальный размер снежинки
                minSpeed: 1, // Минимальная скорость снежинки
                maxSpeed: 1, // Максимальная скорость снежинки
                round: false, // Закруглённые снежинки (true/false)
                shadow: false, // С тенью (true/false)
                deviceorientation: true, // Подстраиваться ли под устройство,
                image: '/images/snowflake_1.png'
            });
";

//Раскоментировать зимой, пойдет снег
//$this->registerJs($js, \yii\web\View::POS_READY)
?>
