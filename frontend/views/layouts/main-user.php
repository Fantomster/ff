<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

frontend\assets\MainAsset::register($this);

$js = <<<JS

function heightDetect() {
		$(".login__block").css("height", $(window).height());
	};
	heightDetect();
	$(window).resize(function() {
		heightDetect();
	});
        
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <link href="css/style.css" rel="stylesheet">
<?php $this->head() ?>
        <!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
<?php $this->beginBody() ?>
        <div class="wrapper">
            <header class="header header__inner_page">
                <div class="inside__block">
                    <div class="container-fluid">
                        <div class="logo__block">
                            <?= Html::a('', Yii::$app->homeUrl, ['class' => 'logo__block-icon']) ?>
                        </div>
                        <div class="phone__block have__quest">
                            <span>Остались вопросы?</span> <span class="phone__block-number"><span class="glyphicon glyphicon-phone"></span>8-499-404-10-18</span>
                        </div>
                        <div class="clear"></div>
                    </div>

                </div>

            </header><!-- .header-->

            <main class="content">
        <?= $content ?>
            </main><!-- .content -->

        </div><!-- .wrapper -->
<?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
