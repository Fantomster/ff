<?php
use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;
use nirvana\showloading\ShowLoadingAsset;
ShowLoadingAsset::register($this);
$this->registerCss('#loader-show {position:absolute;width:100%;height:100%;display:none}');
?>
<div class="content-wrapper">
    <div id="loader-show"></div>
        <?= $content ?>
</div>

<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b><a href="mailto:info@f-keeper.ru" target="_blank"><span class="fa fa-envelope"></span> info@f-keeper.ru</a></b>&nbsp;&nbsp;&nbsp;&nbsp;
        <b><a href="tel:8-499-404-10-18" target="_blank"><span class="glyphicon glyphicon-phone"></span> 8-499-404-10-18</a></b>
    </div>
    <strong>F-keeper.ru &copy; 2016</strong>
</footer>

