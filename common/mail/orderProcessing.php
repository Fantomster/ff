<?php

use common\models\Organization;

$recipientIsClient = ($recipient->organization->type_id == Organization::TYPE_RESTAURANT);
if ($recipientIsClient) {
    if ($isDesadv) {
        $text = $senderOrg->name . " " . Yii::t('app', 'отправил заказ!');
    } else {
        $text = Yii::t('app', 'common.mail.order_confirmed.text_for_client', ['ru' => 'Поставщик {org_name} потдвердил заказ №{order_id}', 'order_id' => $order->id, 'org_name' => $senderOrg->name]);
    }
} else {
    $text = Yii::t('app', 'common.mail.order_confirmed.text_for_vendor', ['ru' => 'Вы подтвердили заказ N°{order_id} для ресторана {org_name}', 'order_id' => $order->id, 'org_name' => $order->client->name]);
}
?>

    <!-- HEADER -->
    <table cellpadding="0" cellspacing="0" border="0" width="100%"
           style="min-width: 340px;line-height: normal;background: #f0f4f2;padding: 16px 0 0;">
        <tr>
            <td align="center" valign="top">
                <table cellpadding="0" cellspacing="0" border="0" width="680"
                       style="border-radius: 4px 4px 0 0;max-width: 680px; min-width: 320px; background: #ffffff;">
                    <tr>
                        <td align="center" valign="top" style="padding: 0 20px;">
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td valign="middle" height="120">
                                        <img src="https://static.mixcart.ru/mix_cart_bezfona.png" alt="logo" width="120"
                                             border="0"
                                             style="border:0; outline:none; text-decoration:none; display:block;">
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;"><?= $text ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="10"></td>
                                </tr>
                                <tr>
                                    <td valign="top"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;"><?= Yii::t('app', 'common.mail.order_changed.string3', ['ru' => 'Для просмотра деталей перейдите в заказ.']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="30"></td>
                                </tr>
                                <tr>
                                    <td align="center" valign="top"
                                        style="color: #ffffff;font-family: 'Open Sans', Arial, sans-serif;text-transform: uppercase;font-size: 16px;">
                                        <a href="<?= $order->getUrlForUser($recipient) ?>" target="_blank" class="btn"
                                           style="font-family: 'Open Sans', Arial, sans-serif;text-decoration: none;color: #ffffff;display: block;width: 230px;height: 50px;font-size: 16px;background-color: #2dbd5c;border-radius: 4px;-webkit-box-shadow: 0px 5px 20px 1px rgba(45, 189, 92, 0.4);-moz-box-shadow: 0px 5px 20px 1px rgba(45, 189, 92, 0.4);box-shadow: 0px 5px 20px 1px rgba(45, 189, 92, 0.4);">
                                            <span style="font-family: 'Open Sans', Arial, sans-serif;color: #ffffff;text-transform: uppercase;font-size: 16px;line-height: 3.3;"><?= Yii::t('app', 'common.mail.order_created.string1', ['ru' => 'Перейти к заказу']) ?></span>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="30"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?= $this->render("_order_footer") ?>