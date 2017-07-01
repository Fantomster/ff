<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'F-keeper';
?>
  <div id="myModal2" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <iframe style="min-width: 320px;width: 100%;" id="cartoonVideo" width="560" height="315" src="https://www.youtube.com/embed/4j5Wam9B5mQ" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
<header class="header" style="background-image: url(/images/header-banner.jpg)">
    <div class="inside__block">
        <div class="site__title"> 
            <h1>Онлайн-сервис 
                для автоматизации закупок<br>
в сфере HoReCa
</h1>
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
                    <img class="hows__banner" src="/images/image-1.png" alt=""/>
                </div>	
            </div>
        </div>
    </div>
    
    <div class="number__block">
        <div class="inside__number-block">
            <div class="container-fluid">
                <div class="col-md-6 col-sm-6">
                    <span class="number"><?=$counter['rest_count'] ?></span>
                    <span class="plays__title">Ресторанов</span>	
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="alig__right">
                        <span class="number"><?=$counter['supp_count'] ?></span>
                        <span class="plays__title">Поставщиков</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rew__block">
        <div class="inside__block">
                <div class="container-fluid">
                    <div id="myCarousel" class="carousel slide" data-ride="carousel">
                      <!-- Indicators -->
                      <ol class="carousel-indicators" style="bottom:-25px">
                        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                        <li data-target="#myCarousel" data-slide-to="1"></li>
                      </ol>
                      <div class="carousel-inner" role="listbox">
                       <div class="item active">
                        <div class="col-md-6">
                                <div class="rew__inside">
                                        <img src="/images/rew2.jpg" alt=""/>
                                        <div class="rew__descript">
                                                <h3>Павел Кравченко</h3>
                                                <span>Управляющий: Винотека.</span>
                                                <p>“Выражаю благодарность f-keeper, за то, что упростили нашу работу в несколько раз. Рекомендую.”</p>
                                        </div>
                                </div>
                        </div>
                        <div class="col-md-6">
                                <div class="rew__inside">
                                        <img src="/images/rew3.jpg" alt=""/>
                                        <div class="rew__descript">
                                                <h3>Христо Дечев</h3>
                                                <span>Совладелец: Black Smith, Гараж, Шайка Лейка, Азия клуб</span>
                                                <p>“Я владею несколькими заведениями. Сначала мы внедрили f-keeper в гараж, а далее во все заведения. Это работает, мы пользуемся и желаем удачи в развитии компании f-keeper.”</p>
                                        </div>
                                </div>
                        </div>
                       </div>
                        <div class="item">
                        <div class="col-md-6">
                                <div class="rew__inside">
                                        <img src="/images/rew4.jpg" alt=""/>
                                        <div class="rew__descript">
                                                <h3>Роман Кудрицкий</h3>
                                                <span>Управляющий: Сорока, МОС.</span>
                                                <p>“Я управляю двумя крупными ресторанами, нам важен высокий уровень как сервиса, так и оптимизации внутренних процессов. F-keeper, решает весь спектр задач, связанных с закупками. Прозрачность и скорость обеспечены.”</p>
                                        </div>
                                </div>
                        </div>
                        <div class="col-md-6">
                                <div class="rew__inside">
                                        <img src="/images/rew5.jpg" alt=""/>
                                        <div class="rew__descript">
                                                <h3>Роман Куча</h3>
                                                <span>Основатель: Brookwin.</span>
                                                <p>“Мой ресторан находится в Анапе, я нахожусь в Москве, сейчас я вижу, что происходит у нас с закупками, кто, что и где покупает. Я за современные инструменты работы, f-keeper, решает мои задачи.”</p>
                                        </div>
                                </div>
                        </div>
                       </div>
                      </div>
                      
                    </div>
                </div>
        </div>
    </div>
    <div class="logo__block_outside">
        <div class="inside__block">
            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="how_its_work">
                        <h3>скоро... Интеграция со всеми системами</h3>
                    </div>
                </div>	
            </div>
        </div>

        <span><img style="filter: none; -webkit-filter:none;" src="/images/1c-logo.png" alt=""/></span>
        <span><img style="filter: none; -webkit-filter:none;" src="/images/iiko.png" alt=""/></span>
        <span><img style="filter: none; -webkit-filter:none;" src="/images/r-keepr-logo.png" alt=""/></span>
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