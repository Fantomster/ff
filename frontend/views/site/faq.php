<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Вопросы / ответы';
?>
<main class="content content-inner ">
    <div class="faq__inner">
        <div class="container-fluid">
            <div class="inside__faq">
                <h2>Ответы на вопросы</h2>
                <span class="faq__inder_title">Никогда закупка не была проще, чем сейчас</span>
                <p>В этом разделе, мы постарались ответить на самые актуальные вопросы, если вы не нашли ответ на свой вопрос, свяжитесь с нами, пожалуйста.</p>
            </div>


            <div class="faq__line"></div>

            <div class="col-md-6 col-sm-6">
                <div class="faq__inside">
                    <h4>Для Ресторанов</h4>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample1">
                        Что нужно, что бы начать работать прямо сейчас? 
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample1">
                        Для того, что бы начать работать прямо сейчас в f-keeper, вам нужно сделать несколько простых шагов. <br/>
                        <?= Html::a('Зарегистрируйтесь', ["/user/register"]) ?>, загрузите каталоги продуктов своих поставщиков, и пригласите их в f-keeper, одним кликом. Вы так же можете выбрать новых поставщиков. С этого момента все ваши закупки вы можете делать в f-keeper.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample2">
                        Сколько это стоит? 
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample2">
                        Бесплатно! В настоящий момент, основной функционал f-keeper, для ресторанов - бесплатный.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample3">
                        Как пригласить своих поставщиков в f-keeper?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample3">
                        Все очень просто. Вы зайдите в раздел "мои поставщики", кликнете на "пригласить поставщика" и вводите контактные данные своего поставщика. На e-mail поставщику придет приглашение от вас. <br/>Если в момент отправки приглашения, поставщик еще не был зарегистрирован в системе, то он будет зарегистрирован автоматически. 
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample4">
                        Возможно ли выставлять лимиты на закупку?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample4">
                        Да, конечно! Вы можете выставить месячный лимит закупки.<br/> При исчерпании лимита, f-keeper сообщит вам об этом. Далее закупки будут возможны только при увеличении лимита или отключении данной функции.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample5">
                        Несколько закупщиков в одном ресторане?	
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample5">
                        Да, в одном ресторане у вас может быть как один, так и несколько закупщиков. Например вы можете разделить закупку продуктов и бара.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample6">
                        Что если у поставщиков различные мин. порог заказа?		
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample6">
                        Если в заказе одновременно участвуют несколько поставщиков, при этом у некоторых из них порог минимального заказа ниже, чем сумма заказа, f-keeper сообщит вам об этом, и подскажет на какую сумму нужно дозаказать, что бы был достигнут минимальный порог.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample7">
                        Что произойдет если заказ не был доставлен?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample7">
                        Если заказ не был доставлен по вине поставщика, вы можете поставить негативную оценку поставщику и оставить честный отзыв о его работе и данном инциденте.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample8">
                        Что если заказ доставлен в ненадлежащем состоянии?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample8">
                        Все то же, что и всегда. Вы можете отказаться от всего заказа или принять заказ частично. При этом поставить оценку о работе поставщика и оставить отзыв.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample9">
                        Могу ли я повторять прошлые заказы?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample9">
                        Да, для того, что бы повторить любой из прошлых заказов, зайдите в раздел "история заказов", выберите необходимый заказ, и отправьте его поставщику вновь. Вы так же можете дополнить заказ или удалить часть, заказываемых продуктов.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample10">
                        Безопасность моих данных?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample10">
                        Мы гарантируем самую высокую степень безопасности хранения всех ваших данных. Также в любой момент по вашей просьбе мы удалим все данные о ваших транзакциях и историю заказов.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample11">
                        Можно ли оплачивать заказы через f-keeper?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample11">
                        Нет. Все финансовые взаимоотношения происходят за пределами f-keeper. Между поставщиков и рестораном.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample12">
                        Что делать, если у меня пока еще нет поставщиков? 
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample12">
                        Если вы недавно открыли ресторан и поставщиков у вас пока еще нет, вы легко можете подобрать подходящих поставщиков.<br/>Зайдите в раздел Market place и выберите подходящих вам поставщиков.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample13">
                        Как посмотреть аналитику всех заказов?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample13">
                        Зайдите в раздел "аналитика" и выберите интересующую вас информацию. Вы можете фильтровать данные по закупщикам, дням, месяца, поставщикам и заказанным продуктам.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample14">
                        Как добавить каталог моего поставщика в f-keeper?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample14">
                        Зайдите в раздел "мои поставщики", выберите пункт меню "добавить поставщика", загрузите каталог поставщика, тот, что у вас есть. Поставщику придет уведомление и он подтвердит, то, что данный каталог, и цены соответствуют вашим договоренностям.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample15">
                        Могу ли я видеть аналитику нескольких своих ресторанов?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample15">
                        Да, конечно. Вы можете добавить в свой аккаунт, нескольких закупщиков, каждый из которых имеет отношение к тому или иному ресторану. Вы так же можете просматривать аналитику по каждому отдельному менеджеру (ресторану), или сделать выборку по всем менеджерам сразу.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample15-2">
                        Интеграция с 1С, iiko, r-keeper
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample15-2">
                        В ближайшем будущем мы разработаем механизмы интеграции с распространенным программным обеспечением по управлению ресторана.
                    </div>
                </div>

            </div>
            <div class="col-md-6 col-sm-6">
                <div class="faq__inside">
                    <h4>Для поставщиков</h4>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample16">
                        Что нужно, что бы начать работать прямо сейчас? 
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample16">
                        <?= Html::a('Зарегистрируйтесь', ["/user/register"]) ?>, перейдите в раздел "мои каталоги" и создайте свой первый каталог с товарами, далее перейдите в раздел "мои клиенты", введите контактные данные своих клиентов и пригласите их в систему.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample17">
                        Сколько это стоит?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample17">
                        До 1 ноября для поставщиков, система f-keeper бесплатна.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample18">
                        Как мне найти новых клиентов в f-keeper?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample18">
                        Размещая свои каталоги продуктов, включите отображение ваших продуктов, на маркет плейсе. Ваши потенциальные клиенты смогут видеть ваши продукты и делать заказы.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample19">
                        Как мне подключить своих, уже существующих клиентов?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample19">
                        Перейдите в раздел "мои клиенты", пригласите ваших клиентов в систему f-keeper. Далее вы сможете регулировать цены для тех или иных клиентов в разделе "мои каталоги".
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample20">
                        Что если у меня несколько менеджеров по работе с заказами?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample20">
                        Конечно! Мы специально разработали функционал, позволяющий управлять обработкой заказов разным менеджерам. <br/>Более того, каждый менеджер может быть привязан к своим клиентам. Видеть обороты своих клиентов, а так же получать информацию о снижении оборотов закупок того или иного клиента. И своевременно отреагировать.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample21">
                        Как мне настроить свой аккаунт и внести информацию о себе?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample21">
                        После регистрации перейдите в раздел "настройки", введите информацию о себе и своей компании, дни отгрузки продуктов, выберите регионы в которые, поставляете.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample22">
                        Для разных клиентов у меня разные цены, как быть с этим?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample22">
                        В разделе "мои каталоги", вы можете создать любое количество разных каталогов. Вы можете создавать каталоги по любым правилам, опускать цены фиксировано, поднимать, или привязывая к процентам. Далее любого своего клиента с любым каталогом.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample23">
                        Если клиент не выплачивает дебиторскую задолженность?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample23">
                        В случае неблагонадежности вашего клиента мы рекомендуем вам поставить оценку и оставить отзыв о данном клиенте. 
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample24">
                        Могу ли я не размещать свои продукты на маркет плейсе? 
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample24">
                        Да, вы можете не отображать все продукты или часть из них на маркет плейсе.<br/> Для того, чтобы скрыть продукты из маркет плейса перейдите в раздел "мои каталоги" войдите в базовый каталог, нажмите "редактировать", и скройте необходимые продукты.<br/> Скрытые продукты будут видны вашим клиентам, но новые клиенты, пришедшие на маркет плейс, не смогут их увидеть.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample25">
                        Безопасность моих данных? 
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample25">
                        Мы гарантируем самую высокую степень безопасности хранения всех ваших данных. Также в любой момент по вашей просьбе мы удалим все данные о ваших транзакциях и историю заказов.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample26">
                        Как разместить свои продукты на маркет плейсе, но скрыть цены?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample26">
                        Да, можете. В разделе "мои каталоги" отредактируйте базовый каталог. Скройте для конкретных продуктов или для всех. При этом ваши текущие клиенты смогут видеть ваши цены. Но имейте в виду, что для новых клиентов, поставщики с открытыми ценами, более привлекательны.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample27">
                        Как посмотреть аналитику всех заказов? 
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample27">
                        Перейдите в раздел "аналитика". Вы можете видеть аналитику по конкретному клиенту, по поставляемым продуктам или по вашим менеджерам. Таким образом вы сможете прозрачно понимать, динамику работы своих менеджеров и клиентов.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample28">
                        Могу ли я видеть аналитику всех цен, всего рынка поставщиков?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample28">
                        В настоящее время нет.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample29">
                        Могут ли мои продукты видеть закупщики из других регионов РФ?
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample29">
                        Да, если вы осуществляете поставки в несколько регионов РФ, то в настройках укажите это. Тогда ваши продукты смогут видеть клиенты из тех регионов, которые вы установили в настройках.
                    </div>
                    <div class="quest__block" data-toggle="collapse" data-target="#collapseExample30">
                        Интеграция с 1С и другими программами.
                        <span class="str"></span>
                    </div>
                    <div class="ser__block collapse" id="collapseExample30">
                        В ближайшем будущем мы разработаем механизмы интеграции с 1С, а так же будет возможность обмена данными через API.
                    </div>


                </div>
            </div>
        </div>
        <div class="clear"></div>

    </div>
</div>
<div class="error__block">
    <p>Нашли ошибку? Помогите нам стать лучше <a href="mailto:hr@f-keeper.ru">hr@f-keeper.ru</a></p>
</div>
</main><!-- .content -->
