<?php
$currencySymbol = $order->currency->iso_code;
?>

<div>
    <div class="pdf_header" style="text-align: center;">
        <span style="font-size: 20px;" >
            <b>Заказ №<?=$order->id?></b>
        </span>
        <br>
        <small>
            от <?=Yii::$app->formatter->asDatetime($order->created_at)?>
        </small>
        <br>
        <small>
            <?= $order->requested_delivery ? 'Запрошенная дата доставки: ' . Yii::$app->formatter->asDate($order->requested_delivery, 'php:d/m/Y') : '' ?>
        </small>
    </div>

    <hr>

    <div class="pdf_content" style="text-align: center;" >

        <table>
            <tr>
                <td>
                    <table style="font-size: 12px;">
                        <tr>
                            <td style="font-size: 14px;"><b>Заказчик</b></td>
                        </tr>
                        <tr>
                            <td><b><?= $order->client->name ?> <?= ($order->client->legal_entity ? "(" .$order->client->legal_entity. ")" : '') ?></b></td>
                        </tr>
                        <tr>
                            <td>
                                Телефон: <?= isset($order->createdByProfile->phone) ? $order->createdByProfile->phone : $order->createdByProfile->phone ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Email: <?= isset($order->createdBy->email) ? $order->createdBy->email : $order->createdBy->email ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Заказ создал: <?= isset($order->createdByProfile->full_name) ? $order->createdByProfile->full_name : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Адрес: <?= $order->client->locality ?>, <?= $order->client->route ?>, <?= $order->client->street_number ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="100"></td>
                <td>
                    <table style="font-size: 12px;">
                        <tr>
                            <td style="font-size: 14px;"><b>Поставщик</b></td>
                        </tr>
                        <tr>
                            <td><b><?= $order->vendor->name ?> <?= ($order->vendor->legal_entity ? "({$order->vendor->legal_entity})" : '') ?></b></td>
                        </tr>
                        <tr>
                            <td>
                                Телефон: <?= isset($order->acceptedByProfile->phone) ? $order->acceptedByProfile->phone : ''?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Email: <?= isset($order->acceptedBy->email) ? $order->acceptedBy->email : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Заказ принял: <?= isset($order->acceptedByProfile->full_name) ? $order->acceptedByProfile->full_name : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Адрес: <?= isset($order->vendor->locality) ? $order->vendor->locality : '' ?>,
                                <?= isset($order->vendor->route) ? $order->vendor->route  : '' ?>,
                                <?= isset($order->vendor->street_number) ? $order->vendor->street_number : '' ?>
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
                    <b>Комментарий к заказу:</b>
                    <p class = "pl" style="padding-top: 5px;">
                        <?= $order->comment ?>
                    </p>
                </div>
            <?php } ?>
            <?= $this->render('_view-grid_pdf', compact('dataProvider', 'order')) ?>
        </div>

        <div style="text-align: right;font-size: 12px;padding-top:30px;padding-right:50px;">
            <?php if ($order->discount) { ?>
                <p>
                    Скидка: <?= $order->getFormattedDiscount(true) ?>
                </p>
            <?php } ?>
            <p>Стоимость доставки: <?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
            <p style="font-size: 14px; font-weight: bold;" >
                ИТОГО: <?= $order->total_price ?> <?= $currencySymbol ?>
            </p>
        </div>

        <div style="display:none;text-align:right; padding-top: 50px;width:100%;">
            <div class = "but_p_1" style="color: #999C9E;display: block;float: left">
                Подпись: ______________ &nbsp;&nbsp;Дата: _________________
            </div>
        </div>
    </div>
</div>