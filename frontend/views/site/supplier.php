<?php
/* @var $this yii\web\View */

$this->title = 'Поставщикам';
?>
<header class="header inner-bg-block" style="background-image: url(/images/restoran-banner-2.jpg)">
    <div class="inside__block">
        <div class="site__title"> 
            <h1>Автоматизация работы с закупщиками.<br/>Закупщики, заказы и аналитика, в одном месте.</h1>
            <h2>Будущее уже сегодня!</h2>
        </div>
    </div>
    <div class="overlay"></div>
</header><!-- .header-->

<main class="content">

    <div class="restoran__content">

        <div class="inside__block">
            <div class="container-fluid">
                <h2>возможности MixCart</h2>
                <span class="for__who">Для поставщиков</span>

                <div class="row">
                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Market Place</span>
                            <img src="/images/rest-1a.png" alt="" />
                            <p>Первый онлайн рынок в России. Ваши продукты увидят все закупщики России, и смогут сделать заказ. При желании вы можете скрыть  свои продукты из общего доступа.</p> 
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Прайсы и каталоги </span>
                            <img src="/images/rest-2a.png" alt="" />
                            <p>Создавайте каталоги, регулируйте все цены сразу или для конкретных закупщиков. Устанавливайте скидки и акции для закупщика, для категории или конкретного продукта.</p> 
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Коммуникации</span>
                            <img src="/images/rest-3a.png" alt="" />
                            <p>Все коммуникации и переписки в одном месте, по каждому конкретному заказу. Никакого человеческого фактора и потери данных.</p> 
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Акции и распродажи</span>
                            <img src="/images/rest-4a.png" alt="" />
                            <p>Вам срочно нужно продать некоторый товар? Создайте акцию, установите лимит по времени и объему. Ваши акции увидят все закупщики.</p> 
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Реклама</span>
                            <img src="/images/rest-5a.png" alt="" />
                            <p>Вам нужен большой поток новых клиентов? Создавайте e-mail рассылки по всем закупщикам, размещайте рекламные баннеры. Все закупщики узнают о вас.</p> 
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Оценки и отзывы</span>
                            <img src="/images/rest-6a.png" alt="" />
                            <p>Вносите вклад в прозрачность. Размешайте отзывы о своих партнерах, оценивайте их работу по достоинству.</p> 
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Аналитика</span>
                            <img src="/images/rest-7a.png" alt="" />
                            <p>Глубокая аналитика всех продаж. История заказов, кто закупает, что, сколько и когда. А так же аналитика работы ваших менеджеров по работе с клиентами.  </p> 
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Участие в тендерах</span>
                            <img src="/images/rest-8a.png" alt="" />
                            <p>Закупщики размещают тендеры, участвуйте в них. Продвигайте свои продукты и завоевывайте доверие.</p> 
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span>Обработка заказов</span>
                            <img src="/images/rest-9a.png" alt="" />
                            <p>Обработка всех заказов онлайн, в одном месте.  Вы видите заказы каждого конкретного закупщика, а так же общий объем отгрузки на выбранный день.</p> 
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <div class="white__bottom_block">
        <div class="inside__wh_block">
            <div class="container-fluid">

                <div class="inside__re">
                    <div class="col-md-4 col-sm-4 block1">
                        <img src="/images/icon-1.jpg" alt=""/>
                        <span>Регистрируйтесь</span>
                    </div>
                    <div class="col-md-4 col-sm-4 block2">
                        <img src="/images/icon-2.jpg" alt=""/>
                        <span>Подключайте поставщиков</span>
                    </div>
                    <div class="col-md-4 col-sm-4 block3">
                        <img src="/images/icon-3.jpg" alt=""/>
                        <span>Заказывайте!</span>
                    </div>
                </div>

                <a class="btn__nav" href="<?= yii\helpers\Url::to(['/user/register']) ?>">начать сейчас</a>

            </div>
        </div>
    </div>
</main><!-- .content -->