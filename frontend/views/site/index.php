<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'F-keeper';
?>
  <div id="myModal2" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <iframe id="cartoonVideo" width="560" height="315" src="https://www.youtube.com/embed/4j5Wam9B5mQ" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
<header class="header" style="background-image: url(images/header-banner.jpg)">
    <div class="inside__block">
        <div class="site__title"> 
            <h1>Автоматизация закупок<br/>между поставщиками и ресторанами</h1>
            <h2>Никогда закупка не была проще, чем сейчас</h2>
        </div>
        <div class="buttons__block">
            <?= Html::a('<span>для ресторанов</span>', ["/site/restaurant"], ['class' => 'for__restaurants']) ?>
<?= Html::a('<span>для поставщиков</span>', ["/site/supplier"], ['class' => 'for__suppliers']) ?>
            <div class="clear"></div>
            <div class="watch_video">
                <a href="#" data-toggle="modal" data-target="#myModal2" ><span class="glyphicon glyphicon-play-circle"></span><span class="watch__span">посмотреть видео</span></a>
            </div>


              
        </div>
    </div>
    <a href="#bottom" class="show__bottom"></a>
    <div class="overlay"></div>
</header><!-- .header-->

<main class="content">
    <div id="bottom" class="white__block">
        <div class="inside__block">
            <div class="container-fluid">
                <div class="col-md-5">
                    <div class="how_its_work">
                        <h3>как это работает</h3>
                        <p>F-keeper это инструмент для автоматизации процесса взаимодействия между поставщиком и рестораном. Рестораны создают заказы, в несколько кликов. Поставщики получают и обрабатывают заказы. Обработка всех заказов, происходит в одном месте. Минимум человеческого фактора. F-keeper, сокращает время на обработку заказов в несколько раз. Уменьшает количество возвратов и ошибок.</p>
<?= Html::a('Для ресторанов', ["/site/restaurant"]) ?> / <?= Html::a('Для поставщиков', ["/site/supplier"]) ?>
                    </div>
                </div>	
                <div class="col-md-7">
                    <img class="hows__banner" src="images/image-1.png" alt=""/>
                </div>	
            </div>
        </div>
    </div>

    <div class="number__block">
        <div class="inside__number-block">
            <div class="container-fluid">
                <div class="col-md-6 col-sm-6">
                    <span class="number">420</span>
                    <span class="plays__title">Ресторанов</span>	
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="alig__right">
                        <span class="number">200</span>
                        <span class="plays__title">Поставщиков</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="logo__block_outside">
        <span><img src="images/logo-1.png" alt=""/></span>
        <span><img src="images/logo-2.png" alt=""/></span>
        <span><img src="images/logo-3.png" alt=""/></span>
        <span><img src="images/logo-4.png" alt=""/></span>
    </div>

    <div class="contact__block">

        <h4>Автоматизируйте свой бизнес сейчас</h4>
        <p>Вы в одном шаге, расскажите о себе</p>
        <div class="contact__form">
<?php if ($flash = Yii::$app->session->getFlash("Register-success")): ?>

                <div class="alert alert-success">
                    <p><?= $flash ?></p>
                </div>

            <?php 
            else:
                echo $this->render('/user/default/_register-form', compact("user", "profile", "organization"));
            endif; 
            ?>
        </div>

    </div>
</main><!-- .content -->
<?php
$this->registerJs('
    var url = $("#cartoonVideo").attr(\'src\');

    $("#myModal2").on(\'hide.bs.modal\', function(){
        $("#cartoonVideo").attr(\'src\', \'\');
    });

    $("#myModal2").on(\'show.bs.modal\', function(){
        $("#cartoonVideo").attr(\'src\', url);
    });     
');
?>