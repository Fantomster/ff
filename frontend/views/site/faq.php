<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('message', 'frontend.views.site.faq', ['ru'=>'Вопросы / ответы']);
?>
<main class="content content-inner ">
    <div class="faq__inner">
        <div class="container-fluid">
            <div class="inside__faq">
                <h2><?= Yii::t('message', 'frontend.views.site.faq_two', ['ru'=>'Ответы на вопросы']) ?></h2>
                <span class="faq__inder_title"><?= Yii::t('message', 'frontend.views.site.never', ['ru'=>'Никогда закупка не была проще, чем сейчас']) ?></span>
                <p><?= Yii::t('message', 'frontend.views.site.actual', ['ru'=>'В этом разделе, мы постарались ответить на самые актуальные вопросы, если вы не нашли ответ на свой вопрос, свяжитесь с нами, пожалуйста.']) ?></p>
            </div>


            <div class="faq__line"></div>

            <div class="col-md-6 col-sm-6">
                <div class="faq__inside">
                    <h4><?= Yii::t('message', 'frontend.views.site.for_rest', ['ru'=>'Для Ресторанов']) ?></h4>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample1">
                        <?= Yii::t('message', 'frontend.views.site.right_now', ['ru'=>'Что нужно, что бы начать работать прямо сейчас?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample1">
                        <?= Yii::t('message', 'frontend.views.site.simple_steps', ['ru'=>'Для того, что бы начать работать прямо сейчас в MixCart, вам нужно сделать несколько простых шагов.']) ?> <br/>
                        <?= Html::a(Yii::t('message', 'frontend.views.site.register', ['ru'=>'Зарегистрируйтесь']), ["/user/register"]) ?><?= Yii::t('message', 'frontend.views.site.downl_cat', ['ru'=>', загрузите каталоги продуктов своих поставщиков, и пригласите их в MixCart, одним кликом. Вы так же можете выбрать новых поставщиков. С этого момента все ваши закупки вы можете делать в MixCart.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample3">
                        <?= Yii::t('message', 'frontend.views.site.invite_vendors', ['ru'=>'Как пригласить своих поставщиков в MixCart?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample3">
                        <?= Yii::t('message', 'frontend.views.site.its_simple', ['ru'=>'Все очень просто. Вы зайдите в раздел "мои поставщики", кликнете на "пригласить поставщика" и вводите контактные данные своего поставщика. На e-mail поставщику придет приглашение от вас. <br/>Если в момент отправки приглашения, поставщик еще не был зарегистрирован в системе, то он будет зарегистрирован автоматически.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample4">
                        <?= Yii::t('message', 'frontend.views.site.limits', ['ru'=>'Возможно ли выставлять лимиты на закупку?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample4">
                        <?= Yii::t('message', 'frontend.views.site.of_course', ['ru'=>'Да, конечно! Вы можете выставить месячный лимит закупки.<br/> При исчерпании лимита, MixCart сообщит вам об этом. Далее закупки будут возможны только при увеличении лимита или отключении данной функции.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample5">
                        <?= Yii::t('message', 'frontend.views.site.several_buyers', ['ru'=>'Несколько закупщиков в одном ресторане?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample5">
                        <?= Yii::t('message', 'frontend.views.site.yep', ['ru'=>'Да, в одном ресторане у вас может быть как один, так и несколько закупщиков. Например вы можете разделить закупку продуктов и бара.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample6">
                        <?= Yii::t('message', 'frontend.views.site.what_if', ['ru'=>'Что если у поставщиков различные мин. порог заказа?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample6">
                        <?= Yii::t('message', 'frontend.views.site.several_vendors', ['ru'=>'Если в заказе одновременно участвуют несколько поставщиков, при этом у некоторых из них порог минимального заказа ниже, чем сумма заказа, MixCart сообщит вам об этом, и подскажет на какую сумму нужно дозаказать, что бы был достигнут минимальный порог.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample7">
                        <?= Yii::t('message', 'frontend.views.site.no_delivery', ['ru'=>'Что произойдет если заказ не был доставлен?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample7">
                        <?= Yii::t('message', 'frontend.views.site.vendors_fault', ['ru'=>'Если заказ не был доставлен по вине поставщика, вы можете поставить негативную оценку поставщику и оставить честный отзыв о его работе и данном инциденте.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample8">
                        <?= Yii::t('message', 'frontend.views.site.bad_conditions', ['ru'=>'Что если заказ доставлен в ненадлежащем состоянии?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample8">
                        <?= Yii::t('message', 'frontend.views.site.like_always', ['ru'=>'Все то же, что и всегда. Вы можете отказаться от всего заказа или принять заказ частично. При этом поставить оценку о работе поставщика и оставить отзыв.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample9">
                        <?= Yii::t('message', 'frontend.views.site.repeat_orders', ['ru'=>'Могу ли я повторять прошлые заказы?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample9">
                        <?= Yii::t('message', 'frontend.views.site.repeat_orders_two', ['ru'=>'Да, для того, что бы повторить любой из прошлых заказов, зайдите в раздел "история заказов", выберите необходимый заказ, и отправьте его поставщику вновь. Вы так же можете дополнить заказ или удалить часть, заказываемых продуктов.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample10">
                        <?= Yii::t('message', 'frontend.views.site.security', ['ru'=>'Безопасность моих данных?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample10">
                        <?= Yii::t('message', 'frontend.views.site.guarantee', ['ru'=>'Мы гарантируем самую высокую степень безопасности хранения всех ваших данных. Также в любой момент по вашей просьбе мы удалим все данные о ваших транзакциях и историю заказов.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample11">
                        <?= Yii::t('message', 'frontend.views.site.mix_pay', ['ru'=>'Можно ли оплачивать заказы через MixCart?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample11">
                        <?= Yii::t('message', 'frontend.views.site.no_payment', ['ru'=>'Нет. Все финансовые взаимоотношения происходят за пределами MixCart. Между поставщиков и рестораном.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample12">
                        <?= Yii::t('message', 'frontend.views.site.no_vendors', ['ru'=>'Что делать, если у меня пока еще нет поставщиков?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample12">
                        <?= Yii::t('message', 'frontend.views.site.marketplace', ['ru'=>'Если вы недавно открыли ресторан и поставщиков у вас пока еще нет, вы легко можете подобрать подходящих поставщиков.<br/>Зайдите в раздел Market place и выберите подходящих вам поставщиков.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample13">
                        <?= Yii::t('message', 'frontend.views.site.analytics', ['ru'=>'Как посмотреть аналитику всех заказов?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample13">
                        <?= Yii::t('message', 'frontend.views.site.choose_info', ['ru'=>'Зайдите в раздел "аналитика" и выберите интересующую вас информацию. Вы можете фильтровать данные по закупщикам, дням, месяца, поставщикам и заказанным продуктам.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample14">
                        <?= Yii::t('message', 'frontend.views.site.add_cat', ['ru'=>'Как добавить каталог моего поставщика в MixCart?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample14">
                        <?= Yii::t('message', 'frontend.views.site.add_cat_two', ['ru'=>'Зайдите в раздел "мои поставщики", выберите пункт меню "добавить поставщика", загрузите каталог поставщика, тот, что у вас есть. Поставщику придет уведомление и он подтвердит, то, что данный каталог, и цены соответствуют вашим договоренностям.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample15">
                        <?= Yii::t('message', 'frontend.views.site.several_rest', ['ru'=>'Могу ли я видеть аналитику нескольких своих ресторанов?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample15">
                        <?= Yii::t('message', 'frontend.views.site.sure', ['ru'=>'Да, конечно. Вы можете добавить в свой аккаунт, нескольких закупщиков, каждый из которых имеет отношение к тому или иному ресторану. Вы так же можете просматривать аналитику по каждому отдельному менеджеру (ресторану), или сделать выборку по всем менеджерам сразу.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample15-2">
                        <?= Yii::t('message', 'frontend.views.site.integration', ['ru'=>'Интеграция с 1С, iiko, r-keeper']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample15-2">
                        <?= Yii::t('message', 'frontend.views.site.future', ['ru'=>'В ближайшем будущем мы разработаем механизмы интеграции с распространенным программным обеспечением по управлению ресторана.']) ?>
                    </div>
                </div>

            </div>
            <div class="col-md-6 col-sm-6">
                <div class="faq__inside">
                    <h4><?= Yii::t('message', 'frontend.views.site.for_vendors', ['ru'=>'Для поставщиков']) ?></h4>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample16">
                        <?= Yii::t('message', 'frontend.views.site.right_now_two', ['ru'=>'Что нужно, что бы начать работать прямо сейчас?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample16">
                        <?= Html::a(Yii::t('message', 'frontend.views.site.register_two', ['ru'=>'Зарегистрируйтесь']), ["/user/register"]) ?><?= Yii::t('message', 'frontend.views.site.my_catalogs', ['ru'=>', перейдите в раздел "мои каталоги" и создайте свой первый каталог с товарами, далее перейдите в раздел "мои клиенты", введите контактные данные своих клиентов и пригласите их в систему.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample18">
                        <?= Yii::t('message', 'frontend.views.site.new_clients', ['ru'=>'Как мне найти новых клиентов в MixCart?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample18">
                        <?= Yii::t('message', 'frontend.views.site.switch_on', ['ru'=>'Размещая свои каталоги продуктов, включите отображение ваших продуктов, на маркет плейсе. Ваши потенциальные клиенты смогут видеть ваши продукты и делать заказы.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample19">
                        <?= Yii::t('message', 'frontend.views.site.invite_clients', ['ru'=>'Как мне подключить своих, уже существующих клиентов?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample19">
                        <?= Yii::t('message', 'frontend.views.site.my_clients', ['ru'=>'Перейдите в раздел "мои клиенты", пригласите ваших клиентов в систему MixCart. Далее вы сможете регулировать цены для тех или иных клиентов в разделе "мои каталоги".']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample20">
                        <?= Yii::t('message', 'frontend.views.site.several_managers', ['ru'=>'Что если у меня несколько менеджеров по работе с заказами?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample20">
                        <?= Yii::t('message', 'frontend.views.site.of_course_two', ['ru'=>'Конечно! Мы специально разработали функционал, позволяющий управлять обработкой заказов разным менеджерам. <br/>Более того, каждый менеджер может быть привязан к своим клиентам. Видеть обороты своих клиентов, а так же получать информацию о снижении оборотов закупок того или иного клиента. И своевременно отреагировать.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample21">
                        <?= Yii::t('message', 'frontend.views.site.self_info', ['ru'=>'Как мне настроить свой аккаунт и внести информацию о себе?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample21">
                        <?= Yii::t('message', 'frontend.views.site.after_register', ['ru'=>'После регистрации перейдите в раздел "настройки", введите информацию о себе и своей компании, дни отгрузки продуктов, выберите регионы в которые, поставляете.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample22">
                        <?= Yii::t('message', 'frontend.views.site.different_prices', ['ru'=>'Для разных клиентов у меня разные цены, как быть с этим?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample22">
                        <?= Yii::t('message', 'frontend.views.site.diff_catalogs', ['ru'=>'В разделе "мои каталоги", вы можете создать любое количество разных каталогов. Вы можете создавать каталоги по любым правилам, опускать цены фиксировано, поднимать, или привязывая к процентам. Далее любого своего клиента с любым каталогом.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample23">
                        <?= Yii::t('message', 'frontend.views.site.debet', ['ru'=>'Если клиент не выплачивает дебиторскую задолженность?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample23">
                        <?= Yii::t('message', 'frontend.views.site.bad_client', ['ru'=>'В случае неблагонадежности вашего клиента мы рекомендуем вам поставить оценку и оставить отзыв о данном клиенте.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample24">
                        <?= Yii::t('message', 'frontend.views.site.may_not_pay', ['ru'=>'Могу ли я не размещать свои продукты на маркет плейсе?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample24">
                        <?= Yii::t('message', 'frontend.views.site.you_can', ['ru'=>'Да, вы можете не отображать все продукты или часть из них на маркет плейсе.<br/> Для того, чтобы скрыть продукты из маркет плейса перейдите в раздел "мои каталоги" войдите в базовый каталог, нажмите "редактировать", и скройте необходимые продукты.<br/> Скрытые продукты будут видны вашим клиентам, но новые клиенты, пришедшие на маркет плейс, не смогут их увидеть.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample25">
                        <?= Yii::t('message', 'frontend.views.site.security_two', ['ru'=>'Безопасность моих данных?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample25">
                        <?= Yii::t('message', 'frontend.views.site.we_guarantee', ['ru'=>'Мы гарантируем самую высокую степень безопасности хранения всех ваших данных. Также в любой момент по вашей просьбе мы удалим все данные о ваших транзакциях и историю заказов.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample26">
                        <?= Yii::t('message', 'frontend.views.site.hide_prices', ['ru'=>'Как разместить свои продукты на маркет плейсе, но скрыть цены?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample26">
                        <?= Yii::t('message', 'frontend.views.site.', ['ru'=>'Да, можете. В разделе "мои каталоги" отредактируйте базовый каталог. Скройте для конкретных продуктов или для всех. При этом ваши текущие клиенты смогут видеть ваши цены. Но имейте в виду, что для новых клиентов, поставщики с открытыми ценами, более привлекательны.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample27">
                        <?= Yii::t('message', 'frontend.views.site.whole_analytics', ['ru'=>'Как посмотреть аналитику всех заказов?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample27">
                        <?= Yii::t('message', 'frontend.views.site.see_anal', ['ru'=>'Перейдите в раздел "аналитика". Вы можете видеть аналитику по конкретному клиенту, по поставляемым продуктам или по вашим менеджерам. Таким образом вы сможете прозрачно понимать, динамику работы своих менеджеров и клиентов.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample28">
                        <?= Yii::t('message', 'frontend.views.site.price_anal', ['ru'=>'Могу ли я видеть аналитику всех цен, всего рынка поставщиков?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample28">
                        <?= Yii::t('message', 'frontend.views.site.not_now', ['ru'=>'В настоящее время нет.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample29">
                        <?= Yii::t('message', 'frontend.views.site.other_regions', ['ru'=>'Могут ли мои продукты видеть закупщики из других регионов РФ?']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample29">
                        <?= Yii::t('message', 'frontend.views.site.several_regions', ['ru'=>'Да, если вы осуществляете поставки в несколько регионов РФ, то в настройках укажите это. Тогда ваши продукты смогут видеть клиенты из тех регионов, которые вы установили в настройках.']) ?>
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample30">
                        <?= Yii::t('message', 'frontend.views.site.1c_integr', ['ru'=>'Интеграция с 1С и другими программами.']) ?>
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample30">
                        <?= Yii::t('message', 'frontend.views.site.1c_future', ['ru'=>'В ближайшем будущем мы разработаем механизмы интеграции с 1С, а так же будет возможность обмена данными через API.']) ?>
                    </div>


                </div>
            </div>
        </div>
        <div class="clear"></div>

    </div>
</div>
<div class="error__block">
    <p><?= Yii::t('message', 'frontend.views.site.find_error', ['ru'=>'Нашли ошибку? Помогите нам стать лучше']) ?> <a href="mailto:hr@mixcart.ru">hr@mixcart.ru</a></p>
</div>
</main><!-- .content -->
