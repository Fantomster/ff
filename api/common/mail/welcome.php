<?php
use yii\helpers\Url;
$organizationType = "client";
if ($type == common\models\Organization::TYPE_SUPPLIER) {
    $organizationType = "vendor";
}
?>
<p style="line-height: 1.6; margin: 0 0 10px; padding: 0;"><img src="https://mixcart.ru/img/immotion1.jpg" style="max-width: 100%; margin: 0; padding: 0;" alt="" /></p>
<h3 style="line-height: 1.1; color: #3f3e3e; font-weight: 500; font-size: 27px; margin: 0 0 15px; padding: 0;">
    <?= Yii::t('app', 'api.common.mail.confirm_email.greetings', ['ru'=>'Приветствую,']) ?> <small style="font-size: 60%; color: #787878; line-height: 0; text-transform: none; margin: 0; padding: 0;"><?= $name ?></small>
</h3>
<p style="line-height: 1.6; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('app', 'api.common.mail.confirm_email.artur', ['ru'=>'Меня зовут Шамалов Артур, я являюсь сооснователем сервиса MixCart.']) ?><br>
    <?= Yii::t('app', 'api.common.mail.confirm_email.thanks', ['ru'=>'Благодарю за подтверждение Вашей учетной записи.']) ?><br>
    ---<br>
    <?= Yii::t('app', 'api.common.mail.confirm_email.tech', ['ru'=>'Технологии стали драйвером развития бизнеса во всех сферах в большинстве стран мира. Нам уже сейчас становится очевидно то, что компании, игнорирующие технологии или скептически к ним настроенные, уйдут с рынка в ближайшие годы, либо им придется адаптироваться под новые реалии.']) ?><br>
    <?= Yii::t('app', 'api.common.mail.confirm_email.i_know', ['ru'=>'Да, я знаю, сначала кажется сложным внедрение новых инструментов, но, поверьте, все намного проще, чем может показаться, и результат того стоит.']) ?><br>
    <?= Yii::t('app', 'api.common.mail.confirm_email.we_made', ['ru'=>'Мы разработали систему MixCart для того, чтобы Вы могли зарабатывать больше денег, тратя свое время на развитие и на важные процессы, оставив рутину нам.']) ?>
   <br> ---<br>
    <?= Yii::t('app', 'api.common.mail.confirm_email.i_glad', ['ru'=>'Я искренне рад видеть Вас в числе наших клиентов и обещаю - мы сделаем все, чтобы превысить Ваши ожидания от работы с нашим сервисом.']) ?><br>
</p>
<?php /*
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">Предлагаю посмотреть обучающие видео и ближе познакомиться с MixCart.</p>
<br style="margin: 0; padding: 0;" />
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <a href="<?= Url::toRoute(["/$organizationType/tutorial"], true); ?>" 
       style="text-decoration: none;
    color: #FFF;
    background-color: #84bf76;
    padding: 10px 16px;
    font-weight: bold;
    margin-right: 10px;
    text-align: center;
    cursor: pointer;
    display: inline-block;
    border-radius: 4px;
    width: 80%;">Видео обучение</a>
</div>
 * 
 */ ?>
<br/><br/>
 <?= Yii::t('app', 'api.common.mail.confirm_email.good_luck', ['ru'=>'Желаю Вам успехов в бизнесе.']) ?>
 <br/><br/>