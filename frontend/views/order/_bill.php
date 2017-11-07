<?php 
$currencySymbol = $order->currency->symbol;
?>
<div class="block_wrapper1" style="width: 100%;-webkit-border-radius: 3px;border-radius: 3px;background-color: #fff;border-top: 3px solid #00a65a;box-shadow: 0 1px 1px rgba(0,0,0,0.1); min-width: 320px;">
    <div class="block__wrapp" style="width: 100%;	height: 100%; padding: 20px;">
        <img  src="<?= Yii::$app->params['pictures']['bill-logo'] ?>" alt="" class="block_logo">

        <div style="width: 100%;">
            <div class="block_new">
                <div style="width: 100%;">
                    <div class="block_name" style="width: 60%;float:left;">
                        <p class = "z_1" style="font-family: Circe_Bold; text-transform: uppercase;font-size: 16px; text-align: left;">Заказчик</p>
                        <p class= "name_dashed" style="font-family: sans-serif;font-size: 18px;border-bottom: 1px dashed #000; width: 100%;text-align: left;font-weight: 100;" ><?= $order->client->name ?></p>
                    </div>
                    <div class="block_img" >
                        <img class = "img_i pull-left" src="<?= $order->client->pictureUrl ?>" alt="" width="93" height="52">
                    </div>
                </div>
                <div class="block_spisok" style="margin-top: 20px; display: block;width:100%; float:left;">
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;" >Город: <?= $order->client->locality ?></p>
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;">Адрес: <?= $order->client->route ?>, <?= $order->client->street_number ?></p>
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;">Телефон: <?= $order->createdByProfile->phone ?></p>
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;">Размещен: <?= $order->createdByProfile->full_name ?></p>
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;">Email:  <?= $order->createdBy->email ?></p>
                    <p style="font-family: Circe_Bold;padding-top: 20px;	text-align: left;color: #999C9E;flex-wrap: bold;">Запрошенная дата доставки: <?= $order->requested_delivery ? Yii::$app->formatter->asDatetime($order->requested_delivery, "php:j M Y") : '' ?></p>
                </div>
            </div>
            <div class="block_new2">
                <div style="width: 100%;">
                    <div class="block_img" >
                        <img  class = "img_i pull-right" src="<?= $order->vendor->pictureUrl ?>" alt="" width="93" height="52">
                    </div>
                    <div class="block_name" style="width: 60%;float:left;">
                        <p  class= "name_p" style="font-family: Circle_Regular; text-transform: uppercase;font-weight: bold;font-size: 16px; text-align: right;">Поставщик</p>
                        <p  class= "name_dashed" style="font-family: sans-serif;font-size: 18px;border-bottom: 1px dashed #000; width: 100%;text-align: right;" ><?= $order->vendor->name ?></p>
                    </div>
                </div>
                <div class="block_spisok" style="margin-top: 20px; display: block;width:100%;float:left;">
                    <p class = "spisok_p" style="font-family: Circle_Regular;	color: #999C9E;text-align: right;" >Город: <?= $order->vendor->locality ?></p>
                    <p class = "spisok_p" style="font-family: Circle_Regular;	color: #999C9E; text-align: right;">Адрес: <?= $order->vendor->route ?>, <?= $order->vendor->street_number ?></p>
                    <p class = "spisok_p"  style="font-family: Circle_Regular;	color: #999C9E;text-align: right;">Телефон: <?= $order->vendor->phone ?></p>
                    <p class = "spisok_p"  style="font-family: Circle_Regular;	color: #999C9E;text-align: right;">Дата создания заказа: <?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
                    <p class = "spisok_p"  style="font-family: Circle_Regular;	color: #999C9E;text-align: right;">Дата доставки: <?= $order->actual_delivery ? Yii::$app->formatter->asDatetime($order->actual_delivery, "php:j M Y") : '' ?></p>
                </div>
            </div>
        </div>

        <?php if (!empty($order->comment)) { ?>
            <div style="width: 100%;height: auto;border: 1px solid #DDDDDD;float: left;margin: 30px 0%; border-radius: 100px;">
                <div style="border-right: 1px solid #DDDDDD;width: 55px;float: left;height: 50px;">
                    <img src="https://mixcart.ru/img/c.png" style="margin-left: 18px;margin-top: 13px;" alt="">
                </div>
                <p class = "pl" style="margin-left: 10px;padding-left: 60px;padding-top: 13px;"><?= $order->comment ?></p>
            </div>
        <?php } ?>
        <?= $this->render('_view-grid', compact('dataProvider', 'order')) ?>
        <div style="width: 100%; height: 170px;">
            <div class = "sp_cen" style="width: 300px;float: right;">
                <?php if ($order->discount) { ?>
                    <p style="text-align: right; color: #82C073;font-size: 16px;background: #F7F7F7;border-bottom: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD; padding: 7px 0;font-family: Circe_Bold">Скидка: <?= $order->getFormattedDiscount() ?></p>
                <?php } ?>
                <p  style="text-align: right;color: #82C073;font-size: 16px;border-bottom: 1px solid #DDDDDD; padding: 7px 0;padding-top: 2px; font-family: Circe_Bold">Стоимость доставки: <?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
                <p  style="text-align: right;color: #82C073;font-size: 16px;border-bottom: 1px solid #DDDDDD;  padding: 7px 0;padding-top: 2px; font-family: Circe_Bold">Итого: <?= $order->total_price ?> <?= $currencySymbol ?></p>
            </div>
        </div>
        <div style="padding-bottom: 20px;width:100%;">
            <div class = "but_p_1" style="color: #999C9E;display: block;float: left">Подпись: ______________ &nbsp;&nbsp;Дата: _________________</div>
        </div>
    </div>
</div>