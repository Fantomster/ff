<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tbody>
        <tr>
            <td align="center" valign="top">
                <table cellpadding="0" cellspacing="0" border="0" width="680"
                       style="max-width: 680px; min-width: 320px; background: #ffffff;">
                    <tr>
                        <td align="center" valign="middle" height="320"
                            style="width: 100%;background-image: url('https://preview.ibb.co/bRS9nH/banner_fruits.png');background-repeat: no-repeat;background-position: 0 -15px;background-size: cover;">
                            <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 40px;color: #ffffff;font-weight: bold;text-transform: uppercase;">
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 40px;color: #ffffff;line-height: normal;">
                                <?= Yii::t('app', 'common.mail.welcome.head_good', ['ru' => 'Здорово,']) ?>
                            </span>
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 40px;color: #ffffff;line-height: normal;display: block;width: 100%;">
                                <?= Yii::t('app', 'common.mail.welcome.head_good_1', ['ru' => 'Что вы с нами']) ?>
                            </span>
                            </font>
                            <span style="height: 115px;display: block;width: 100%;"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="20"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="580"
                   style="max-width: 580px; min-width: 320px; background: #ffffff;">
                <tr>
                    <td valign="middle" height="70"
                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                        <font face="'Open Sans', Arial, sans-serif"
                              style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;font-weight: 600;">
                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;color: #2a2c2e;display: block;">
                            <?= Yii::t('app', 'common.mail.welcome.hello', ['ru' => 'Здравствуйте!']) ?>
                        </span>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td valign="bottom"
                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                            <?= Yii::t('app', 'common.mail.welcome.full_text', ['ru' => '<p style="font-family: \'Open Sans\', Arial, sans-serif;font-size: 16px;margin: 0;">Меня зовут
                            Ильдар Хасанов, я являюсь сооснователем сервиса MixCart. Я искренне рад видеть Вас в числе
                            наших клиентов!</p>
                        <p style="font-family: \'Open Sans\', Arial, sans-serif;font-size: 16px;">Технологии уже давно
                            стали драйвером развития бизнеса во всех сферах. С MixCart рестораны и поставщики управляют
                            закупками и делают это быстрее, удобнее, и с большей обоюдной выгодой для бизнеса.</p>
                        <p style="font-family: \'Open Sans\', Arial, sans-serif;font-size: 16px;">Да, я знаю, сначала
                            внедрение новых инструментов кажется сложным, но, поверьте, все намного проще, чем может
                            показаться, и результат того стоит!</p>']) ?>
                    </td>
                </tr>
                <tr>
                    <td height="10"></td>
                </tr>
                <tr>
                <tr>
                    <td align="center" valign="top" height="90" class="btn-wrapper"
                        style="color: #ffffff;font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;">
                        <a href="<?= \Yii::$app->urlManagerFrontend->baseUrl ?>" target="_blank"
                           style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;text-decoration: none;color: #ffffff;display: block;width: 280px;background-image: url('https://image.ibb.co/eydtxH/btn_hover.png');background-position: -3px -12px;background-repeat: no-repeat;height: 83px;">
                            <font face="'Open Sans', Arial, sans-serif"
                                  style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;color: #ffffff;">
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;line-height: 60px;color: #ffffff;">
                                <?= Yii::t('app', 'common.mail.welcome.start', ['ru' => 'Начнем']) ?>
                            </span>
                            </font>
                        </a>
                    </td>
                </tr>
    </tr>
    <tr>
        <td height="10"></td>
    </tr>
    <tr>
        <td valign="bottom"
            style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
            <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;margin:0;">
                <?= Yii::t('app', 'common.mail.welcome.read_instruction', ['ru' => 'Ознакомьтесь, пожалуйста, с инструкцией по работе с MixCart:']) ?>
            </p>
            <ul>
                <li>
                    <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;margin:0;">
                        <?= Yii::t('app', 'common.mail.welcome.instruction_restaurant', ['ru' => 'для ресторанов']) ?>
                        <a href="<?= \Yii::$app->params['help']['restoran'] ?>" target="_blank"
                           style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color:#00a9ff;"><?= \Yii::$app->params['help']['restoran'] ?></a>
                    </p></li>
                <li>
                    <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;margin:0;">
                        <?= Yii::t('app', 'common.mail.welcome.instruction_supplier', ['ru' => 'для поставщиков']) ?>
                        <a href="<?= \Yii::$app->params['help']['vendor'] ?>" target="_blank"
                           style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color:#00a9ff"><?= \Yii::$app->params['help']['vendor'] ?></a>
                    </p></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="580" style="max-width: 580px; min-width: 320px; background: #ffffff;">
                <tbody><tr>
                        <td valign="middle" height="170" style="width: 120px;">
                            <img src="https://static.mixcart.ru/ildar.jpg" alt="Ильдар Хасанов" border="0">
                        </td>
                        <td valign="middle">
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><?= Yii::t('app', 'common.mail.welcome.sign_1', ['ru' => 'Ильдар Хасанов']) ?></span><br>
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><?= Yii::t('app', 'common.mail.welcome.sign_2', ['ru' => 'Сооснователь MixCart']) ?></span><br>
                            <a href="<?= \Yii::$app->urlManagerFrontend->baseUrl ?>" target="_blank" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #00a9ff;"><span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #00a9ff;"><?= \Yii::$app->urlManagerFrontend->baseUrl ?></span></a>
                        </td>
                    </tr>
                </tbody></table>
        </td>
    </tr>
</table>
</td>
</tr>
</table>

<?= $this->renderAjax('layouts/mail_footer', ['user' => $user ?? null]) ?>
