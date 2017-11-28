<?php
/* @var $this yii\web\View */

$this->title = Yii::t('message', 'frontend.views.site.for_rest_three', ['ru'=>'Ресторанам']);
?>
<header class="header inner-bg-block" style="background-image: url(/images/restoran-banner.jpg)">
    <div class="inside__block">
        <div class="site__title"> 
            <h1><?= Yii::t('message', 'frontend.views.site.revolution', ['ru'=>'Революция в работе с поставщиками<br/>Закупка в 2 клика.']) ?></h1>
            <h2><?= Yii::t('message', 'frontend.views.site.never_earlier', ['ru'=>'Никогда закупка не была проще, чем сейчас']) ?></h2>
        </div>
    </div>
    <div class="overlay"></div>
</header><!-- .header-->


<main class="content">

    <div class="restoran__content">

        <div class="inside__block">
            <div class="container-fluid">
                <h2><?= Yii::t('message', 'frontend.views.site.abilities', ['ru'=>'возможности MixCart']) ?></h2>
                <span class="for__who"><?= Yii::t('message', 'frontend.views.site.for_rest_four', ['ru'=>'Для ресторанов']) ?></span>

                <div class="row">
                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.all_together', ['ru'=>'Все поставщики в одном месте']) ?></span>
                            <img src="/images/rest-1.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.all_vendors', ['ru'=>'В MixCart, вы можете видеть всех поставщиков, и выбрать для себя тех, кто соответствует вашим критериям.']) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.transparency', ['ru'=>'Прозрачность']) ?></span>
                            <img src="/images/rest-2.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.bad_service', ['ru'=>'Вас не устраивает сервис поставщика или качество продуктов? Оставьте отзыв, поставьте оценку.']) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.two_clicks', ['ru'=>'Закупка в 2 клика']) ?></span>
                            <img src="/images/rest-3.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.no_calls', ['ru'=>'Больше никаких звонков, таблиц и прочих, устаревших инструментов. Создавайте заказы в несколько кликов.']) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.full_anal', ['ru'=>'Подробная аналитика']) ?></span>
                            <img src="/images/rest-4.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.form_report', ['ru'=>'Подробная аналитика позволит знать, что, сколько и у кого вы закупаете. Формировать и выгружать подробные отчеты.']) ?> </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.orders_history', ['ru'=>'История заказов']) ?></span>
                            <img src="/images/rest-5.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.see_history', ['ru'=>'Просматривайте историю заказов. Создавайте новые заказы или повторяйте предыдущие. Это сокращает время на закупку в несколько раз.']) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.vendors_sales', ['ru'=>'Распродажи поставщиков']) ?></span>
                            <img src="/images/rest-6.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.sale_buy', ['ru'=>'Закупайте по акции. Все акции и распродажи поставщиков в одном месте. Выгодно вам, выгодно им.']) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.set_limits', ['ru'=>'Выставление лимитов']) ?></span>
                            <img src="/images/rest-7.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.limit_value', ['ru'=>'Вы можете ограничить объем закупок определенным лимитом. Закупщик не сможет сделать закупку больше лимита.']) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.tenders', ['ru'=>'Размещение тендеров']) ?></span>
                            <img src="/images/rest-8.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.good_prices', ['ru'=>'Вы можете разместить тендер на закупку тех или иных продуктов, и поставщики сами предложат вам максимально выгодные для вас цены.']) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rest__item">
                            <span><?= Yii::t('message', 'frontend.views.site.one_place', ['ru'=>'Коммуникации в одном месте']) ?></span>
                            <img src="/images/rest-9.png" alt="" />
                            <p><?= Yii::t('message', 'frontend.views.site.no_human', ['ru'=>'Никакого человеческого фактора. Все коммуникации по каждому заказу в одном месте. Никакой потери информации больше не будет.']) ?></p>
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
                        <span><?= Yii::t('message', 'frontend.views.site.register_three', ['ru'=>'Регистрируйтесь']) ?></span>
                    </div>
                    <div class="col-md-4 col-sm-4 block2">
                        <img src="/images/icon-2.jpg" alt=""/>
                        <span><?= Yii::t('message', 'frontend.views.site.incl_vendors', ['ru'=>'Подключайте поставщиков']) ?></span>
                    </div>
                    <div class="col-md-4 col-sm-4 block3">
                        <img src="/images/icon-3.jpg" alt=""/>
                        <span><?= Yii::t('message', 'frontend.views.site.order_it', ['ru'=>'Заказывайте!']) ?></span>
                    </div>
                </div>

                <a class="btn__nav" href="<?= yii\helpers\Url::to(['/user/register']) ?>"><?= Yii::t('message', 'frontend.views.site.begin_now', ['ru'=>'начать сейчас']) ?></a>

            </div>
        </div>
    </div>
</main><!-- .content -->