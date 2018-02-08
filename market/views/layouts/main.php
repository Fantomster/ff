<?php
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

kartik\growl\GrowlAsset::register($this);
market\assets\AppAsset::register($this);
//\yii\bootstrap\BootstrapAsset::register($this);
\yii\bootstrap\BootstrapPluginAsset::register($this);

$addAction = Url::to(["site/ajax-add-to-cart"]);
$inviteAction = Url::to(["site/ajax-invite-vendor"]);
$customJs = <<< JS
var wow = new WOW(
  {
    boxClass:     'wow',      // animated element css class (default is wow)
    animateClass: 'animated', // animation css class (default is animated)
    offset:       11,          // distance to the element when triggering the animation (default is 0)
    mobile:       false,       // trigger animations on mobile devices (default is true)
    live:         true,       // act on asynchronously loaded content (default is true)
  }
);
wow.init();       
JS;
$this->registerJs($customJs, View::POS_READY);
$js = <<<JS
        if(Cookies.get('left-menu')){
        $("#accordion .panel-collapse").removeClass('in');
        $("#" + Cookies.get('left-menu')).addClass("in");
        }
        $(document).on("click", ".add-to-cart", function(e) {
            e.preventDefault();
            $.post(
                "$addAction",
                {product_id: $(this).data("product-id")}
            ).done(function (result) {
                    $.notify(result.growl.options, result.growl.settings);
            });
        });
        $(document).on("click", ".invite-vendor", function(e) {
            e.preventDefault();
            $.post(
                "$inviteAction",
                {vendor_id: $(this).data("vendor-id")}
            ).done(function (result) {
                    $.notify(result.growl.options, result.growl.settings);
            });
        });
        $('#accordion').on('hidden.bs.collapse', function (e) {
            Cookies.remove('left-menu');
        })
        $('#accordion').on('shown.bs.collapse', function (e) {
            Cookies.set('left-menu', e.target.id);
        })
        
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <link rel="shortcut icon" href="/images/favicon/favicon.ico" type="image/x-icon">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="shortcut icon" href="/images/favicon/favicon.ico" type="image/x-icon">
    <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <?php $this->head() ?>
</head>
<body>  
<?php $this->beginBody() ?> 
<?= $this->render('header.php') ?>
<?= $this->render('search.php') ?>
    
<section  id="features1-u" style="background:url(/fmarket/images/linen.jpg) repeat">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="row">
          <div class="col-md-4 right-padding" style="margin-bottom: 30px;" >
            <?= $this->render('left.php') ?>
          </div>
          <div class="col-md-8">
            <?= $this->render('content.php',['content' => $content]) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<a id='backTop'>Back To Top</a>
<?php
if (Yii::$app->params['enableYandexMetrics']) {
    echo $this->render('_yandex');
}
$this->endBody()
?>
</body>
</html>
<?php $this->endPage() ?>      