<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */


    dmstr\web\AdminLteAsset::register($this);
    frontend\assets\AppAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <link rel="shortcut icon" href="/images/favicon/favicon.ico" type="image/x-icon">
        <?php $this->head() ?>
    </head>
    <body class="hold-transition skin-blue sidebar-mini <?=Yii::$app->session->get('sidebar-collapse')?'sidebar-collapse':''?>">
    <?php $this->beginBody() ?>
    <div class="wrapper" style="margin-bottom: -20px;overflow-y:hidden;">
    
        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>
        <?= \frontend\widgets\cart\CartWidget::widget(['orders' => $this->params['orders']])?>
        <?= !Yii::$app->user->isGuest ? $this->render(
            'client/left',
            ['directoryAsset' => $directoryAsset]
        ) : ''
        ?>
        <?= $this->render(
            'content.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>
        
    </div>
<?php

//echo $this->render('_yandex');
echo $this->render('_yandex_client');

$sidebarUrl = Url::to(['client/sidebar']);


        $js = <<<JS
$('.sidebar-toggle').on('click', function(e){
    $.post("$sidebarUrl", {"sidebar-collapse": true})
});       
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>
