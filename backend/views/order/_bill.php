<div class="col-sm-12">
    <div class="col-sm-2">
        <div class="panel_white_border_green">
            <div class="row_field">
                <div class="title"><?= Yii::t('app', 'Статус') ?>:</div>
                <span>
                    <?= $order->getStatusText(); ?>
                </span>
            </div>
            <div class="row_field">
                <div class="title"><?= Yii::t('app', 'Дата создания') ?>:</div>
                <span>
                    <?= Yii::$app->formatter->asTime($order->created_at, "php:j M Y, H:i:s") ?>
                </span>
            </div>
        </div>
    </div>
    <div class="col-sm-5">
        <div class="panel_white_border_green">
            <div class="col-sm-12">
                <p class="name_label"><?= Yii::t('app', 'Заказчик') ?></p>
                <a href="/organization/<?= $order->client->id ?>" class="name_title">
                    <?= $order->client->name ?>
                </a>
            </div>

            <div class="body">
                <p><?= Yii::t('app', 'Город') ?>: <?= $order->client->locality ?></p>
                <p><?= Yii::t('app', 'Адрес') ?>: <?= $order->client->routeText ?>
                    , <?= $order->client->streetText ?></p>
                <p><?= Yii::t('app', 'Телефон') ?>: <?= $order->createdByProfile->phone ?></p>
                <p><?= Yii::t('app', 'Размещен') ?>: <?= $order->createdByProfile->full_name ?></p>
                <p><?= Yii::t('app', 'Email') ?>: <?= $order->createdBy->email ?></p>
                <p>
                    <?= Yii::t('app', 'Запрошенная дата доставки') ?>:
                    <?= $order->requested_delivery
                        ?
                        Yii::$app->formatter->asDatetime($order->requested_delivery, "php:d.m.Y")
                        : ''
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-sm-5">
        <div class="panel_white_border_green">
            <div class="col-sm-12">
                <p class="name_label"><?= Yii::t('app', 'Поставщик') ?></p>
                <a href="/organization/<?= $order->vendor->id ?>" class="name_title"><?= $order->vendor->name ?></a>
            </div>

            <div class="body">
                <p><?= Yii::t('app', 'Город') ?>: <?= $order->vendor->locality ?></p>
                <p><?= Yii::t('app', 'Адрес') ?>: <?= $order->vendor->routeText ?>
                    , <?= $order->client->streetText ?></p>
                <p><?= Yii::t('app', 'Телефон') ?>: <?= $order->vendor->phone ?></p>
                <p>
                    <?= Yii::t('app', 'Дата создания заказа') ?>:
                    <?= Yii::$app->formatter->asDatetime($order->created_at, "php:d.m.Y") ?>
                </p>
                <p>
                    <?= Yii::t('app', 'Дата доставки') ?>:
                    <?= $order->actual_delivery
                        ?
                        Yii::$app->formatter->asDatetime($order->actual_delivery, "php:d.m.Y")
                        : ''
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($order->comment)) { ?>
    <div class="col-sm-12 order_comment_block">
        <div><img src="https://app.mixcart.ru/img/c.png"></div>
        <p><?= $order->comment ?></p>
    </div>
<?php } ?>

<div class="col-sm-12" style="margin-top: 20px;">
    <?= $this->render('_view-grid', compact('dataProvider', 'order')) ?>
</div>

<div class="col-sm-4 pull-right">
    <table class="table table-bordered">
        <?php if ($order->discount > 0) { ?>
            <tr>
                <td><b><?= Yii::t('app', 'Скидка') ?></b></td>
                <td><?= $order->getFormattedDiscount() . ' ' . $order->currency->symbol ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td><b><?= Yii::t('app', 'Стоимость доставки') ?></b></td>
            <td><?= $order->calculateDelivery() . ' ' . $order->currency->symbol ?></td>
        </tr>
        <tr>
            <td><b><?= Yii::t('app', 'Итого') ?></b></td>
            <td><b><?= $order->total_price . ' ' . $order->currency->symbol ?></b></td>
        </tr>
    </table>
</div>