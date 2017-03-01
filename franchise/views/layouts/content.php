<?php
use yii\web\View;
use nirvana\showloading\ShowLoadingAsset;
ShowLoadingAsset::register($this);
$this->registerCss('#loader-show {position:fixed;width:100%;height:100%;left:0;top:0;z-index:9999;display:none;}');
?>
<div id="loader-show"></div>
<div class="content-wrapper">
        <?= $content ?>
</div>