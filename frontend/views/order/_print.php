<?php
$canRepeatOrder = false;
?>
<div class="box box-info">
    <div class="box-header with-border">
        <h4 class="font-bold">Заказ №<?= $order->id ?></h4><hr>
        <div class="row" style="line-height: 1.8; font-size: 10px !important">
            <table width="100%">
                <tr><td width="50%">
                        <span class="org-type">Заказчик:</span><br>
                        <?= $order->client->name ?><br><br>
                        <address>
                            <b>Город:</b> <?= $order->client->city ?><br>
                            <b>Адрес:</b> <?= $order->client->address ?><br>
                            <b>Телефон:</b> <?= $order->client->phone ?>
                        </address>
                        <p class="text-left">
                            <b>Размещен:</b>
                            <?= $order->createdBy->profile->full_name ?><br>
                            <b>Email:</b> <?= $order->createdBy->email ?>
                        </p>
                        <p class="pull-right text-left">
                            <strong>Запрошенная дата доставки:</strong><br>
                            <?= $order->requested_delivery ? Yii::$app->formatter->asDatetime($order->requested_delivery, "php:j M Y") : '' ?>
                        </p>
                    </td><td width="50%" class="text-right">
                        <span class="org-type">Поставщик:</span><br>
                        <?= $order->vendor->name ?><br><br>
                        <address>
                            <b>Город:</b> <?= $order->vendor->city ?><br>
                            <b>Адрес:</b> <?= $order->vendor->address ?><br>
                            <b>Телефон:</b> <?= $order->vendor->phone ?>
                        </address>
                        <p class="text-right">
                            <span><strong>Дата создания заказа:</strong><br><?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></span><br>
                            <span><strong>Дата доставки:</strong><br><?= $order->actual_delivery ? Yii::$app->formatter->asDatetime($order->actual_delivery, "php:j M Y") : '' ?></span>
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