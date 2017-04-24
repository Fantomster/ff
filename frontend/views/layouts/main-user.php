<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

frontend\assets\MainAsset::register($this);

$js = <<<JS

function heightDetect() {
		$(".login__block").css("height", $(window).height());
	};
	//heightDetect();
	$(window).resize(function() {
		//heightDetect();
	});
            $("#menu__burger").click(function () {
                $(".nav_menu").slideToggle();
            });
        
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <link href="<?= yii\helpers\Url::base() ?>/css/style.css" rel="stylesheet">
        <link rel="shortcut icon" href="images/favicon/favicon.ico" type="image/x-icon">
        <?php $this->head() ?>
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition skin-blue sidebar-mini" style="background: url(../images/login-bg.jpg) no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;">
        <?php $this->beginBody() ?>
        <div id="loader-show"></div>

        <div class="wrapper">
            <main class="content">
                <?= $content ?>
            </main><!-- .content -->

        </div><!-- .wrapper -->

        <?php
        echo $this->render('_yandex');
        $this->endBody()
        ?>
    </body>
</html>
<?php $this->endPage() ?>
