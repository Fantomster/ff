<?php
use yii\helpers\Html;

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
        <link rel="shortcut icon" href="images/favicon/favicon.ico" type="image/x-icon">
        <?php $this->head() ?>
    </head>
    <body class="hold-transition skin-blue sidebar-mini <?=Yii::$app->session->get('sidebar-collapse')?'sidebar-collapse':''?>">
    <?php $this->beginBody() ?>
    <div class="wrapper">
    
        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>

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
 echo $this->render('_yandex');
        $js = <<<JS
$('.sidebar-toggle').on('click', function(e){
    $.post("index.php?r=client/sidebar", {"sidebar-collapse": true})
});       
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
    <?php $this->endBody() ?>
        <!-- BEGIN JIVOSITE CODE {literal} -->
<script type='text/javascript'>
(function(){ var widget_id = 'RI0YDaTCe9';
var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);})();</script>
<!-- {/literal} END JIVOSITE CODE -->
    </body>
    </html>
    <?php $this->endPage() ?>
