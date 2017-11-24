<?php
$canRepeatOrder = false;
?>
<div class="box box-info">
    <div class="box-header with-border">
        <h4 class="font-bold"><?= Yii::t('message', 'frontend.views.order.order_no', ['ru'=>'Заказ №']) ?><?= $order->id ?></h4><hr>
        <div class="row" style="line-height: 1.8; font-size: 10px !important">
            <table width="100%">
                <tr><td width="50%">
                        <span class="org-type"><?= Yii::t('message', 'frontend.views.order.orderer', ['ru'=>'Заказчик:']) ?></span><br>
                        <?= $order->client->name ?><br><br>
                        <address>
                            <b><?= Yii::t('message', 'frontend.views.order.city', ['ru'=>'Город:']) ?></b> <?= $order->client->city ?><br>
                            <b><?= Yii::t('message', 'frontend.views.order.address', ['ru'=>'Адрес:']) ?></b> <?= $order->client->address ?><br>
                            <b><?= Yii::t('message', 'frontend.views.order.phone', ['ru'=>'Телефон:']) ?></b> <?= $order->client->phone ?>
                        </address>
                        <p class="text-left">
                            <b><?= Yii::t('message', 'frontend.views.order.settled', ['ru'=>'Размещен:']) ?></b>
                            <?= $order->createdBy->profile->full_name ?><br>
                            <b><?= Yii::t('message', 'frontend.views.order.email', ['ru'=>'Email:']) ?></b> <?= $order->createdBy->email ?>
                        </p>
                        <p class="pull-right text-left">
                            <strong><?= Yii::t('message', 'frontend.views.order.delivery_date_two', ['ru'=>'Запрошенная дата доставки:']) ?></strong><br>
                            <?= $order->requested_delivery ? Yii::$app->formatter->asDatetime($order->requested_delivery, "php:j M Y") : '' ?>
                        </p>
                    </td><td width="50%" class="text-right">
                        <span class="org-type"><?= Yii::t('message', 'frontend.views.order.vendor_three', ['ru'=>'Поставщик:']) ?></span><br>
                        <?= $order->vendor->name ?><br><br>
                        <address>
                            <b><?= Yii::t('message', 'frontend.views.order.city_two', ['ru'=>'Город:']) ?></b> <?= $order->vendor->city ?><br>
                            <b><?= Yii::t('message', 'frontend.views.order.address_two', ['ru'=>'Адрес:']) ?></b> <?= $order->vendor->address ?><br>
                            <b><?= Yii::t('message', 'frontend.views.order.phone_two', ['ru'=>'Телефон:']) ?></b> <?= $order->vendor->phone ?>
                        </address>
                        <p class="text-right">
                            <span><strong><?= Yii::t('message', 'frontend.views.order.creating_date_two', ['ru'=>'Дата создания заказа:']) ?></strong><br><?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></span><br>
                            <span><strong><?= Yii::t('message', 'frontend.views.order.delivery_date_three', ['ru'=>'Дата доставки:']) ?></strong><br><?= $order->actual_delivery ? Yii::$app->formatter->asDatetime($order->actual_delivery, "php:j M Y") : '' ?></span>
                        </p>
                    </td></tr>
            </table>
            <br>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div id="orderGrid">
            <?=
            $this->render('_view-grid', compact('dataProvider', 'order', 'canRepeatOrder'));
            ?>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>