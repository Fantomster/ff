<?php
$currencySymbol = $order->currency->iso_code;
?>

<div>
    <div class="pdf_header" style="text-align: center;border-bottom: 3px solid black;margin-bottom: 20px;">
        <span style="font-size: 20px;" >
            <b>Заказ №<?=$order->id?></b>
        </span>
        <br>
        <small>
            от <?=Yii::$app->formatter->asDatetime($order->created_at)?>
        </small>
        <br>
        <small>
            <?= $order->requested_delivery ? 'Запрошенная дата доставки: ' . Yii::$app->formatter->asDatetime($order->requested_delivery) : '' ?>
        </small>
    </div>
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
                                Телефон: <?= $order->createdByProfile->phone ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Email: <?= $order->createdBy->email ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Заказ создал: <?= $order->createdByProfile->full_name ?>
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
                            <td><b><?= $order->vendor->name ?> <?= ($order->vendor->legal_entity ? "($order->vendor->legal_entity)" : '') ?></b></td>
                        </tr>
                        <tr>
                            <td>
                                Телефон: <?= $order->acceptedByProfile->phone ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Email: <?= $order->acceptedBy->email ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Заказ создал: <?= $order->acceptedByProfile->full_name ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Адрес: <?= $order->vendor->locality ?>, <?= $order->vendor->route ?>, <?= $order->vendor->street_number ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="margin-top: 30px;">
            <?= $this->render('_view-grid_pdf', compact('dataProvider', 'order')) ?>
        </div>
        <?php if (!empty($order->comment)) { ?>
            <div style="font-size: 12px;width: 100%;height: auto;border: 1px solid grey;text-align:left;padding: 5px;margin-bottom: 10px;">
                Комментарий к заказу:
                <p class = "pl" style="margin-left: 10px;padding-left: 60px;padding-top: 13px;">
                    <?= $order->comment ?>
                </p>
            </div>
        <?php } ?>

        <div style="text-align: right;font-size: 12px;">
            <?php if ($order->discount) { ?>
                <p>
                    Скидка: <?= $order->getFormattedDiscount() ?>
                </p>
            <?php } ?>
            <p>Стоимость доставки: <?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
            <p style="font-size: 14px; font-weight: bold;" >
                ИТОГО: <?= $order->total_price ?> <?= $currencySymbol ?>
            </p>
        </div>

        <div style="text-align:right; padding-top: 50px;width:100%;">
            <div class = "but_p_1" style="color: #999C9E;display: block;float: left">
                Подпись: ______________ &nbsp;&nbsp;Дата: _________________
            </div>
        </div>
    </div>
</div>