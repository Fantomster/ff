<?php 
$currencySymbol = $order->currency->symbol;
?>
<div class="block_wrapper1" style="width: 100%;-webkit-border-radius: 3px;border-radius: 3px;background-color: #fff;border-top: 3px solid #00a65a;box-shadow: 0 1px 1px rgba(0,0,0,0.1); min-width: 320px;">
    <div class="block__wrapp" style="width: 100%;	height: 100%; padding: 20px;">
        <?php if(!empty($order->invoice)):?>
            <blockquote >
                Заказ создан на основании накладной 1С
                <?php if(!empty($order->invoice->orderRelation)):?>
                    <?php $link = \yii\helpers\Html::a($order->invoice->orderRelation->id, '/order/' . $order->invoice->orderRelation->id);?>
                    (первичный заказ <?=$link?>)
                <?php endif;?>
            </blockquote >
        <?php endif;?>
        <?php if(!empty($order->invoiceRelation)):?>
            <blockquote >
                <?php $link = \yii\helpers\Html::a($order->invoiceRelation->order_id, '/order/' . $order->invoiceRelation->order_id);?>
                Cоздан новый заказ <?=$link?> на основании накладной 1С
            </blockquote >
        <?php endif;?>
        <img  src="<?= Yii::$app->params['pictures']['bill-logo'] ?>" alt="" class="block_logo">

        <div style="width: 100%;">
            <div class="block_new">
                <div style="width: 100%;">
                    <div class="block_name" style="width: 60%;float:left;">
                        <p class = "z_1" style="font-family: Circe_Bold; text-transform: uppercase;font-size: 16px; text-align: left;"><?= Yii::t('message', 'frontend.views.order.customer', ['ru'=>'Заказчик']) ?></p>
                        <p class= "name_dashed" style="font-family: sans-serif;font-size: 18px;border-bottom: 1px dashed #000; width: 100%;text-align: left;font-weight: 100;" ><?= $order->client->name ?></p>
                    </div>
                    <div class="block_img" >
                        <img class = "img_i pull-left" src="<?= $order->client->pictureUrl ?>" alt="" width="93" height="52">
                    </div>
                </div>
                <div class="block_spisok" style="margin-top: 20px; display: block;width:100%; float:left;">
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;" ><?= Yii::t('message', 'frontend.views.order.city_three', ['ru'=>'Город:']) ?> <?= $order->client->locality ?></p>
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;"><?= Yii::t('message', 'frontend.views.order.address_three', ['ru'=>'Адрес:']) ?> <?= $order->client->route ?>, <?= $order->client->street_number ?></p>
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;"><?= Yii::t('message', 'frontend.views.order.phone_three', ['ru'=>'Телефон:']) ?> <?= $order->createdByProfile->phone ?></p>
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;"><?= Yii::t('message', 'frontend.views.order.settled', ['ru'=>'Размещен:']) ?> <?= $order->createdByProfile->full_name ?></p>
                    <p style="font-family: Circe_Bold;	color: #999C9E;flex-wrap: bold;text-align: left;"><?= Yii::t('message', 'frontend.views.order.email_two', ['ru'=>'Email:']) ?>  <?= $order->createdBy->email ?></p>
                    <p style="font-family: Circe_Bold;padding-top: 20px;	text-align: left;color: #999C9E;flex-wrap: bold;"><?= Yii::t('message', 'frontend.views.order.requested_delivery', ['ru'=>'Запрошенная дата доставки:']) ?> <?= $order->requested_delivery ? Yii::$app->formatter->asDatetime($order->requested_delivery, "php:j M Y") : '' ?></p>
                </div>
            </div>
            <div class="block_new2">
                <div style="width: 100%;">
                    <div class="block_img" >
                        <img  class = "img_i pull-right" src="<?= $order->vendor->pictureUrl ?>" alt="" width="93" height="52">
                    </div>
                    <div class="block_name" style="width: 60%;float:left;">
                        <p  class= "name_p" style="font-family: Circle_Regular; text-transform: uppercase;font-weight: bold;font-size: 16px; text-align: right;"><?= Yii::t('message', 'frontend.views.order.vendor_four', ['ru'=>'Поставщик']) ?></p>
                        <p  class= "name_dashed" style="font-family: sans-serif;font-size: 18px;border-bottom: 1px dashed #000; width: 100%;text-align: right;" ><?= $order->vendor->name ?></p>
                    </div>
                </div>
                <div class="block_spisok" style="margin-top: 20px; display: block;width:100%;float:left;">
                    <p class = "spisok_p" style="font-family: Circle_Regular;	color: #999C9E;text-align: right;" ><?= Yii::t('message', 'frontend.views.order.city_four', ['ru'=>'Город:']) ?> <?= $order->vendor->locality ?></p>
                    <p class = "spisok_p" style="font-family: Circle_Regular;	color: #999C9E; text-align: right;"><?= Yii::t('message', 'frontend.views.order.address_four', ['ru'=>'Адрес:']) ?> <?= $order->vendor->route ?>, <?= $order->vendor->street_number ?></p>
                    <p class = "spisok_p"  style="font-family: Circle_Regular;	color: #999C9E;text-align: right;"><?= Yii::t('message', 'frontend.views.order.phone_four', ['ru'=>'Телефон:']) ?> <?= $order->vendor->phone ?></p>
                    <p class = "spisok_p"  style="font-family: Circle_Regular;	color: #999C9E;text-align: right;"><?= Yii::t('message', 'frontend.views.order.creating_date_four', ['ru'=>'Дата создания заказа:']) ?> <?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
                    <p class = "spisok_p"  style="font-family: Circle_Regular;	color: #999C9E;text-align: right;"><?= Yii::t('message', 'frontend.views.order.delivery_date', ['ru'=>'Дата доставки:']) ?> <?= $order->actual_delivery ? Yii::$app->formatter->asDatetime($order->actual_delivery, "php:j M Y") : '' ?></p>
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
                    <p style="text-align: right; color: #82C073;font-size: 16px;background: #F7F7F7;border-bottom: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD; padding: 7px 0;font-family: Circe_Bold"><?= Yii::t('message', 'frontend.views.order.discount_two', ['ru'=>'Скидка:']) ?> <?= $order->getFormattedDiscount() ?></p>
                <?php } ?>
                <p  style="text-align: right;color: #82C073;font-size: 16px;border-bottom: 1px solid #DDDDDD; padding: 7px 0;padding-top: 2px; font-family: Circe_Bold"><?= Yii::t('message', 'frontend.views.order.delivery_price_four', ['ru'=>'Стоимость доставки:']) ?> <?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
                <p  style="text-align: right;color: #82C073;font-size: 16px;border-bottom: 1px solid #DDDDDD;  padding: 7px 0;padding-top: 2px; font-family: Circe_Bold"><?= Yii::t('message', 'frontend.views.order.total_price', ['ru'=>'Итого:']) ?> <?= $order->total_price ?><?= $currencySymbol ?></p>
            </div>
        </div>
        <div style="padding-bottom: 20px;width:100%;">
            <div class = "but_p_1" style="color: #999C9E;display: block;float: left"><?= Yii::t('message', 'frontend.views.order.sign', ['ru'=>'Подпись:']) ?> ______________ &nbsp;&nbsp;<?= Yii::t('message', 'frontend.views.order.date', ['ru'=>'Дата:']) ?> _________________</div>
        </div>
    </div>
</div>
