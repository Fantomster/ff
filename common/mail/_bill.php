<?php
    $currencySymbol = $order->currency->symbol;
?>
<div style="width:100%;-webkit-border-radius: 3px;border-radius: 3px;background-color: #fff;border-top: 3px solid #00a65a;box-shadow: 0 1px 1px rgba(0,0,0,0.1); width: 570px; margin-top: 10px;">
    <div style="width:100%;height: 100%;">
        <table style="padding:10px;width:550px;border-collapse: collapse;border: 0;">
            <tr>
                <td colspan="2" style="width:50%;text-align:left;"><span style="font-family: Circe_Bold; text-transform: uppercase;font-weight:bold;font-size: 16px; text-align: left;"><?= Yii::t('app', 'common.mail.bill.client', ['ru'=>'Заказчик']) ?></span></td>
                <td colspan="2" style="width:50%;text-align:right;"><span style="font-family: Circe_Bold; text-transform: uppercase;font-weight:bold;font-size: 16px; text-align: right;"><?= Yii::t('app', 'common.mail.bill.vendor', ['ru'=>'Поставщик']) ?></span></td>
            </tr><tr>
                <td style="width:165px;vertical-align: bottom;"><p style="font-family: sans-serif;font-size: 18px;border-bottom: 1px dashed #000;text-align: left;"><?= $order->client->name ?></p></td>
                <td style="width:110px;"><img src="<?= $order->client->pictureUrl ?>" alt="" width="93" height="52" style="float:left;"></td>
                <td style="width:110px;"><img src="<?= $order->vendor->pictureUrl ?>" alt="" width="93" height="52" style="float:right;"></td>
                <td style="width:165px;vertical-align: bottom;"><p style="font-family: sans-serif;font-size: 18px;border-bottom: 1px dashed #000;text-align: right;"><?= $order->vendor->name ?></p></td>
            </tr><tr>
                <td colspan="2" style="width:50%;vertical-align: top;">
                    <p style="font-family: Circe_Bold;color: #999C9E;flex-wrap: bold;text-align: left;" ><?= Yii::t('app', 'common.mail.bill.city', ['ru'=>'Город:']) ?> <?= $order->client->locality ?></p>
                    <p style="font-family: Circe_Bold;color: #999C9E;flex-wrap: bold;text-align: left;"><?= Yii::t('app', 'common.mail.bill.address', ['ru'=>'Адрес:']) ?> <?= $order->client->route ?>, <?= $order->client->street_number ?></p>
                    <p style="font-family: Circe_Bold;color: #999C9E;flex-wrap: bold;text-align: left;"><?= Yii::t('app', 'common.mail.bill.phone', ['ru'=>'Телефон:']) ?> <?= $order->createdByProfile->phone ?></p>
                    <p style="font-family: Circe_Bold;color: #999C9E;flex-wrap: bold;text-align: left;"><?= Yii::t('app', 'common.mail.bill.settled', ['ru'=>'Размещен:']) ?> <?= $order->createdByProfile->full_name ?></p>
                    <p style="font-family: Circe_Bold;color: #999C9E;flex-wrap: bold;text-align: left;"><?= Yii::t('app', 'common.mail.bill.email', ['ru'=>'Email:']) ?>  <?= $order->createdBy->email ?></p>
                    <p style="font-family: Circe_Bold;padding-top: 20px;text-align: left;color: #999C9E;flex-wrap: bold;"><?= Yii::t('app', 'common.mail.bill.delivery_date', ['ru'=>'Запрошенная дата доставки:']) ?> <?= $order->requested_delivery ? Yii::$app->formatter->asDatetime($order->requested_delivery, "php:j M Y") : '' ?></p>
                </td><td colspan="2" style="width:50%;vertical-align: top;">
                    <p style="font-family: Circle_Regular;color: #999C9E;text-align: right;" ><?= Yii::t('app', 'common.mail.bill.city_two', ['ru'=>'Город:']) ?> <?= $order->vendor->locality ?></p>
                    <p style="font-family: Circle_Regular;color: #999C9E;text-align: right;"><?= Yii::t('app', 'common.mail.bill.address_two', ['ru'=>'Адрес:']) ?> <?= $order->vendor->route ?>, <?= $order->vendor->street_number ?></p>
                    <p style="font-family: Circle_Regular;color: #999C9E;text-align: right;"><?= Yii::t('app', 'common.mail.bill.phone_two', ['ru'=>'Телефон:']) ?> <?= $order->vendor->phone ?></p>
                    <p style="font-family: Circle_Regular;color: #999C9E;text-align: right;"><?= Yii::t('app', 'common.mail.bill.creation_date', ['ru'=>'Дата создания заказа:']) ?> <?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
                    <p style="font-family: Circle_Regular;color: #999C9E;text-align: right;"><?= Yii::t('app', 'common.mail.bill.delivery_date_two', ['ru'=>'Дата доставки:']) ?> <?= $order->actual_delivery ? Yii::$app->formatter->asDatetime($order->actual_delivery, "php:j M Y") : '' ?></p>
                </td>
            </tr>
        </table>
        <?php if (!empty($order->comment)) { ?>
            <div style="width: 100%;height: auto;border: 1px solid #DDDDDD;float: left;margin: 30px 0%; border-radius: 100px;">
                <div style="border-right: 1px solid #DDDDDD;width: 55px;float: left;height: 50px;">
                    <img src="https://mixcart.ru/img/c.png" style="margin-left: 18px;margin-top: 13px;" alt="">
                </div>
                <p class = "pl" style="margin-left: 10px;padding-left: 60px;padding-top: 13px;"><?= $order->comment ?></p>
            </div>
            <?php
        }
        if (count($order->orderChat)) {
            ?>
            <div style="width: 100%;height: auto;border: 1px solid #DDDDDD;float: left;margin: 30px 0%; border-radius: 100px;">
                <div style="border-right: 1px solid #DDDDDD;width: 55px;float: left;height: 50px;">
                    <img src="https://mixcart.ru/img/c.png" style="margin-left: 18px;margin-top: 13px;" alt="">
                </div>
                <div style="padding: 20px 60px 20px 60px;text-align:left;">
                    <?php foreach ($order->orderChat as $message) { ?>
                        <p class = "pl" style="padding-left: 10px;padding-top: 5px;">
                            <?= "<span style='color: #a4a4a4'>(" . Yii::$app->formatter->asTime($message->created_at, "php:j M Y, H:i:s") . ")</span> " . ($message->is_system ? '' : "<b>" . $message->sentBy->profile->full_name . "</b>: ") . $message->message ?>
                        </p>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        <?= $this->render('_view-grid', compact('dataProvider', 'order')) ?>
        <div style="width: 600px;text-align: right;">
            <?php if ($order->discount) { ?>
                <p style="width:300px;color: #82C073;font-size: 16px;background: #F7F7F7;border-bottom: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD; padding: 7px 0;font-family: Circe_Bold;"><?= Yii::t('app', 'common.mail.bill.discount', ['ru'=>'Скидка:']) ?> <?= $order->getFormattedDiscount() ?></p>
            <?php } ?>
            <p style="width:300px;color: #82C073;font-size: 16px;border-bottom: 1px solid #DDDDDD; padding: 7px 0;padding-top: 2px; font-family: Circe_Bold;margin-left:auto;margin-right:30px;"><?= Yii::t('app', 'common.mail.bill.delivery_price', ['ru'=>'Стоимость доставки:']) ?> <?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
            <p style="width:300px;color: #82C073;font-size: 16px;border-bottom: 1px solid #DDDDDD; padding: 7px 0;padding-top: 2px; font-family: Circe_Bold;margin-left:auto;margin-right:30px;"><?= Yii::t('app', 'common.mail.bill.total', ['ru'=>'Итого:']) ?> <?= $order->total_price ?> <?= $currencySymbol ?></p>
        </div>
        <p style="width:600px;color: #999C9E;margin-top: 150px;display: block;text-align: left;margin-top: 10px;"><?= Yii::t('app', 'common.mail.bill.subscribe', ['ru'=>'Подпись:']) ?> ______________ &nbsp;<?= Yii::t('app', 'common.mail.bill.date', ['ru'=>'Дата:']) ?> _________________</p>
    </div>
</div>