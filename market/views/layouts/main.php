<?php
use yii\helpers\Html;

use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

market\assets\AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
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
    
<section  id="features1-u">
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
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
