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
    <div class="pull-right hidden-xs">
        <b><a href="mailto:info@mixcart.ru" target="_blank"><span class="fa fa-envelope"></span> info@mixcart.ru</a></b>&nbsp;&nbsp;&nbsp;&nbsp;
        <b><a href="tel:8-499-404-10-18" target="_blank"><span class="glyphicon glyphicon-phone"></span> 8-499-404-10-18</a></b>
    </div>
    <strong>mixcart.ru &copy; <?= date('Y') ?></strong>
</footer>
