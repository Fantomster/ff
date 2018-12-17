<?php
/* @var $this \yii\web\View */
/* @var $content string */

use api\assets\AppAsset;
use yii\helpers\Html;
// use yii\bootstrap\Nav;
// use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use nirvana\showloading\ShowLoadingAsset;

ShowLoadingAsset::register($this);
$this->registerCss('#loader-show {position:absolute;width:100%;display:none;}');

AppAsset::register($this);

$customJs = <<< JS
$('#loader-show').css('height',$(window).height());
$(window).on('resize',function() {
    $('#loader-show').css('height',$(window).height());
});
JS;
$this->registerJs($customJs, yii\web\View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"> 
       <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>
    <div class="container">
                <?=
                Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ])
                ?>
          
                <?= $content ?>
            </div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
