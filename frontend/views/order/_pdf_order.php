<?php
$currencySymbol = $order->currency->iso_code;
?>

<div>
    <div class="pdf_header" style="text-align: center;">
        <span style="font-size: 20px;" >
            <b><?= Yii::t('message', 'frontend.views.order.order_no', ['ru'=>'Заказ №']) ?><?=$order->id?></b>
        </span>
        <br>
        <small>
            <?= Yii::t('message', 'frontend.views.order.pdf_ot', ['ru'=>'от']) ?>
            <?=Yii::$app->formatter->asDatetime($order->created_at, 'php:d.m.Y, H:i')?>
        </small>
        <br>
        <small>
            <?= $order->requested_delivery ? Yii::t('message', 'frontend.views.order.delivery_date_two', ['ru'=>'Запрошенная дата доставки:']) . Yii::$app->formatter->asDate($order->requested_delivery, 'php:d.m.Y') : '' ?>
        </small>
    </div>

    <hr>

    <div class="pdf_content" style="text-align: center;" >

        <table>
            <tr>
                <td>
                    <table style="font-size: 12px;">
                        <tr>
                            <td style="font-size: 14px;">
                                <b>
                                    <?= Yii::t('message', 'frontend.views.order.orderer', ['ru'=>'Заказчик:']) ?>
                                </b>
                            </td>
                        </tr>
                        <tr>
                            <td><b><?= $order->client->name ?> <?= ($order->client->legal_entity ? "(" .$order->client->legal_entity. ")" : '') ?></b></td>
                        </tr>
                        <tr>
                            <td>
                                <?= Yii::t('message', 'frontend.views.order.phone', ['ru'=>'Телефон']) ?>:
                                <?= isset($order->createdByProfile->phone) ? $order->createdByProfile->phone : $order->createdByProfile->phone ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Email: <?= isset($order->createdBy->email) ? $order->createdBy->email : $order->createdBy->email ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?= Yii::t('message', 'frontend.views.order.user_order_create', ['ru'=>'Заказ создал']) ?>:
                                <?= isset($order->createdByProfile->full_name) ? $order->createdByProfile->full_name : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?= Yii::t('message', 'frontend.views.order.address', ['ru'=>'Адрес']) ?>:
                                <?= $order->client->locality ?>, <?= $order->client->route ?>, <?= $order->client->street_number ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="100"></td>
                <td>
                    <table style="font-size: 12px;">
                        <tr>
                            <td style="font-size: 14px;"><b><?= Yii::t('app', 'Поставщик') ?></b></td>
                        </tr>
                        <tr>
                            <td><b><?= $order->vendor->name ?> <?= ($order->vendor->legal_entity ? "(" . $order->vendor->legal_entity . ")" : '') ?></b></td>
                        </tr>
                        <tr>
                            <td>
                                <?= Yii::t('message', 'frontend.views.order.phone_two') ?>:
                                <?= isset($order->acceptedByProfile->phone) ? $order->acceptedByProfile->phone : ''?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Email: <?= isset($order->acceptedBy->email) ? $order->acceptedBy->email : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?= Yii::t('message', 'frontend.views.order.order_accept') ?>:
                                <?= isset($order->acceptedByProfile->full_name) ? $order->acceptedByProfile->full_name : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?= Yii::t('message', 'frontend.views.order.address_two') ?>:
                                <?= isset($order->vendor->locality) ? $order->vendor->locality : '' ?>
                                <?= isset($order->vendor->route) ? ', '.$order->vendor->route  : '' ?>
                                <?= (isset($order->vendor->street_number) && $order->vendor->street_number != 'undefined') ? ', '.$order->vendor->street_number : '' ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <hr>

        <div>
            <?php if (!empty($order->comment)) { ?>
                <div style="font-size: 12px;width: 100%;height: auto;text-align:left;padding: 5px;margin-bottom: 10px;">
                    <b style="font-size: 14px;" >
                        <?= Yii::t('message', 'frontend.views.order.order_comment') ?>
                    </b>
                    <p class = "pl" style="padding-top: 5px;">
                        <?= $order->comment ?>
                    </p>
                </div>
            <?php } ?>
            <?= $this->render('_view-grid_pdf', compact('dataProvider', 'order')) ?>
        </div>

        <div style="text-align: right;font-size: 12px;padding-top:10px;">
            <?php if ($order->discount) { ?>
                <p>
                    <?= Yii::t('message', 'frontend.views.order.discount') ?>:
                    <?= $order->getFormattedDiscount(true) ?>
                </p>
            <?php } ?>
            <p>
                <?= Yii::t('message', 'frontend.views.order.delivery_price') ?> <?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
            <p style="font-size: 14px; font-weight: bold;" >
                <?= mb_strtoupper(Yii::t('message', 'frontend.views.order.total_price')) ?> <?= $order->total_price ?> <?= $currencySymbol ?>
            </p>
        </div>

        <div style="text-align:right; padding-top: 10px; width:100%;">
            <div class = "but_p_1" style="display: block;float: left;font-size: 12px;">
                <?= Yii::t('message', 'frontend.views.order.signature') ?>: ______________
                &nbsp;
                <?= Yii::t('message', 'frontend.views.order.current_date') ?>: _________________
            </div>
        </div>
    </div>
</div>