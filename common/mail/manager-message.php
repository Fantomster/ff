<?php
/**
 * @var \common\models\User $user
 */
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="680" style="max-width: 580px; min-width: 320px; background: #ffffff;">
                <tr>
                    <td style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.manager_message.greetings', ['ru' => 'Здравствуйте!']) ?></p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.manager_message.paragraph1', ['ru' => 'Меня зовут Оля, я менеджер по развитию в MixCart. Недавно Вы зарегистрировались на нашей платформе, и я хочу помочь Вам разобраться во всех её возможностях.']) ?></p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.manager_message.two_words', ['ru' => 'В двух словах — что можно делать в MixCart:']) ?></p>
                        <ol style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                            <li><?= Yii::t('app', 'common.mail.manager_message.li1', ['ru' => 'Вести все заказы и рабочие процессы по закупкам в одной системе и в мобильном приложении']) ?></li>
                            <li><?= Yii::t('app', 'common.mail.manager_message.li2', ['ru' => 'Интегрировать закупки с документооборотом (iiko, R-keeper, 1С)']) ?></li>
                            <li><?= Yii::t('app', 'common.mail.manager_message.li3', ['ru' => 'Быстро находить продукты и сравнивать цены']) ?></li>
                            <li><?= Yii::t('app', 'common.mail.manager_message.li4', ['ru' => 'Только для поставщиков: стать нашим партнером и размещать продукты на MixMarket']) ?></li>
                        </ol>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.manager_message.paragraph2', ['ru' => 'Мне было бы очень интересно пообщаться с Вами и обсудить методы, которыми Вы пользуетесь в управлении закупками, возникающие сложности. Я могу рассказать, как MixCart будет полезен в Вашем конкретном случае.']) ?></p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.manager_message.farewell', ['ru' => 'Отличного дня,']) ?></p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;margin: 0;"><?= Yii::t('app', 'common.mail.manager_message.manager_name', ['ru' => 'Ольга Захряпина']) ?></p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;margin: 0;"><?= Yii::t('app', 'common.mail.manager_message.manager_title', ['ru' => 'Менеджер по развитию']) ?></p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;margin: 0;">
                            <?php if ($user->language == 'ru') { ?>
                                <a href="https://mixcart.ru/" target="_blank" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">https://mixcart.ru/</a>
                            <?php } else { ?>
                                <a href="https://mix-cart.com/" target="_blank" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">https://mix-cart.com/</a>
                            <?php } ?>
                        </p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;margin: 0;"><?= Yii::t('app', 'common.mail.manager_message.manager_phone', ['ru' => '8-499-404-10-18']) ?></p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.manager_message.ps', ['ru' => 'P.S. Мы постоянно добавляем новые возможности, так что следите за новостями в']) ?> <a href="https://blog.mixcart.ru/" target="_blank" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                                <?= Yii::t('app', 'common.mail.manager_message.link_text', ['ru' => 'блоге']) ?>блоге</a>!</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
