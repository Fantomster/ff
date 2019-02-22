<?php

use common\models\Organization;
use common\helpers\MailHelper;
use common\models\Role;

$currencySymbol = $order->currency->symbol;
$senderIsClient = ($senderOrg->type_id == Organization::TYPE_RESTAURANT);
$recipientIsClient = ($recipient->organization->type_id == Organization::TYPE_RESTAURANT);
$recipientIsFranchisee = isset($recipient->role_id) && in_array($recipient->role_id, [Role::ROLE_FRANCHISEE_OWNER, Role::ROLE_FRANCHISEE_LEADER, Role::ROLE_FRANCHISEE_MANAGER]);
$self = MailHelper::isSelf($senderOrg, $recipient);
$orgType = $senderIsClient ? Yii::t('app', 'common.mail.order_created.rest', ['ru' => "Ресторан"]) : Yii::t('app', 'common.mail.order_created.vendor', ['ru' => "Поставщик"]);

$lastMessage = $order->getOrderChatLastMessage();
$messageTitle = "";
$messageList = "";
if (!empty($lastMessage)) {
    $messageTitle = $self ? Yii::t('app', 'common.mail.order_changed.self_title', ['ru' => 'Вы изменили детали заказа №']) : Yii::t('app', 'common.mail.order_changed.another_party_title', ['ru' => '{org_name} изменил детали заказа №', 'org_name' => $senderOrg->getName()]);
    $messageTitle .= $order->id . ":";
    $messageList = substr($lastMessage->message, strpos($lastMessage->message, "<ul"));
    $ulStyle = "padding: 0; margin: 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;";
    $liStyle = "margin: 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;";
    $messageList = str_replace("{ul_style}", $ulStyle, str_replace("{li_style}", $liStyle, $messageList));
    $messageList = str_replace('<span class="value-old">', '<span style="color: grey; text-decoration: line-through; font-size: 12px;">', $messageList);
}
?>
    <!-- ШАПКА -->
    <table cellpadding="0" cellspacing="0" border="0" width="100%"
           style="min-width: 340px;line-height: normal;background-color: #f0f4f2;width: 100%;padding: 20px 0 0;">
        <tr>
            <td align="center" valign="top">
                <table cellpadding="0" cellspacing="0" border="0" width="680"
                       style="border-radius: 4px 4px 0 0;max-width: 680px; min-width: 320px; background: #ffffff;border-radius: 4px;">
                    <tr>
                        <td align="center" valign="top" style="padding: 0 20px 16px 20px;">
                            <table cellpadding="0" cellspacing="0" width="100%" style="border-radius: 4px;">
                                <tr>
                                    <td valign="middle" height="120">
                                        <img src="https://static.mixcart.ru/mix_cart_bezfona.png" alt="logo" width="120"
                                             border="0" style="border:0;display:block;">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;"><?= $messageTitle ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;padding: 0 0 0 20px;">
                                        <?= $messageList ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="middle"
                                        style="padding: 20px 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;">
                                    <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;margin: 0;">
                                        <?= $recipientIsClient ? Yii::t('app', 'common.mail.order_changed.string1', ['ru' => 'Измененный заказ вы можете просмотреть ниже.']) : Yii::t('app', 'common.mail.order_changed.string3', ['ru' => 'Для просмотра деталей перейдите в заказ.']) ?>
                                    </span>
                                    </td>
                                </tr>
                                <tr>
                                    <?php if ($recipientIsClient) { ?>
                                        <td valign="middle"
                                            style="font-family: 'Open Sans', Arial, sans-serif;color: #8c8f8d;font-size: 16px;">
                                            <span style="font-family: 'Open Sans', Arial, sans-serif;color: #8c8f8d;font-size: 16px;"><?= Yii::t('app', 'common.mail.order_changed.string2', ['ru' => 'Информация об изменениях была отправлена поставщику по электронной почте и SMS.']) ?></span>
                                        </td>
                                    <?php } ?>
                                    <td align="center" valign="top"
                                        style="color: #ffffff;font-family: 'Open Sans', Arial, sans-serif;text-transform: uppercase;font-size: 16px;">
                                        <a href="<?= $order->getUrlForUser($recipient, Yii::$app->params['app_version']) ?>"
                                           target="_blank" class="btn"
                                           style="font-family: 'Open Sans', Arial, sans-serif;text-decoration: none;color: #ffffff;display: block;width: 230px;height: 50px;font-size: 16px;background-color: #2dbd5c;border-radius: 4px;-webkit-box-shadow: 0px 5px 20px 1px rgba(45, 189, 92, 0.4);-moz-box-shadow: 0px 5px 20px 1px rgba(45, 189, 92, 0.4);box-shadow: 0px 5px 20px 1px rgba(45, 189, 92, 0.4);">
                                            <span style="font-family: 'Open Sans', Arial, sans-serif;color: #ffffff;text-transform: uppercase;font-size: 16px;line-height: 3.3;"><?= Yii::t('app', 'common.mail.order_created.string1', ['ru' => 'Перейти к заказу']) ?></span>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td height="5"></td>
        </tr>
    </table>

    <!-- ТЕЛО -->
<?php if ($recipientIsClient) { ?>
    <table cellpadding="0" cellspacing="0" border="0" width="100%"
           style="min-width: 340px;line-height: normal;background-color: #f0f4f2;">
        <tr>
            <td align="center" valign="top">
                <table cellpadding="0" cellspacing="0" border="0" width="680"
                       style="max-width: 680px; min-width: 320px; background: #ffffff;border-radius: 4px;">
                    <tr>
                        <td valign="top" style="padding: 16px 0 0 20px;">
                            <table width="400" style="max-width: 400px;">
                                <tr>
                                    <td colspan="2" valign="top"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;font-weight: 700;color: #000000;"><?= Yii::t('app', 'common.mail.order_processing.order_no', ['ru' => 'Заказ №']) . $order->id ?></span>
                                    </td>
                                    <td colspan="1" valign="bottom"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;line-height: 1.7;"><?= Yii::$app->formatter->asDatetime($order->created_at, "php:d.m.Y, H:i") ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" valign="top"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 700;color: #f5a623;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 700;color: #f5a623;"><?= Yii::t('app', 'common.models.order_status.status_awaiting_accept_from_vendor', ['ru' => 'Ожидает потверждения поставщика']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20"></td>
                                </tr>
                                <tr>
                                    <td valign="top"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 600;color: #8c8f8d;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 600;color: #8c8f8d;"><?= Yii::t('message', 'frontend.views.order.full_sum', ['ru' => 'Общая сумма']) ?></span>
                                    </td>
                                    <td valign="top"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 600;color: #8c8f8d;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 600;color: #8c8f8d;"><?= Yii::t('app', 'common.mail.order.delivery_date', ['ru' => 'Дата доставки']) ?></span>
                                    </td>
                                    <td valign="top"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 600;color: #8c8f8d;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 600;color: #8c8f8d;">
                                            <?= Yii::t('app', 'common.mail.bill.discount', ['ru' => 'Скидка:']) ?> <b
                                                    style="font-size: 16px;color: #000000;line-height: 1;"><?= $order->discount ? $order->getFormattedDiscount() : 0 ?></b>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="30" valign="middle"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 700;color: #000000;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 700;color: #000000;"><?= $order->total_price ?> <?= $currencySymbol ?></span>
                                    </td>
                                    <td height="30" align="center" valign="middle"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-weight: 700;color: #000000;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-weight: 700;color: #000000;"><?= $order->requested_delivery ? Yii::$app->formatter->asDatetime($order->requested_delivery, "php:d.m.y") : '' ?></span>
                                    </td>
                                    <td height="30" valign="middle"
                                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 600;color: #8c8f8d;">
                                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 600;color: #8c8f8d;"><?= Yii::t('message', 'frontend.views.vendor.delivery', ['ru' => 'Доставка']) ?>: <b
                                                    style="font-size: 14px;color: #000000;line-height: 14px;"><?= $order->calculateDelivery() ?> <?= $currencySymbol ?></b></span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td valign="top" style="padding: 16px 20px 0 0;">
                            <?= $recipientIsClient ? $this->render("_new_client", compact("order")) : $this->render("_new_vendor", compact("order")) ?>
                        </td>
                    </tr>
                    <?php if (!empty($order->comment)) { ?>
                        <tr>
                            <td colspan="4" valign="top"
                                style="font-family: 'Open Sans', Arial, sans-serif;font-size: 18px;font-weight: 700;color: #000000;padding: 0 20px 20px;">
                                <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 18px;font-weight: 700;color: #000000;"><?= Yii::t('message', 'frontend.views.request.order_comment', ['ru' => 'Комментарий к заказу']) ?></span>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="4"
                            style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-style: italic; color: #8c8f8d; padding: 0 20px 26px;">
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-style: italic; color: #8c8f8d;">
                                <?= $order->comment ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" valign="top">
                <table cellpadding="0" cellspacing="0" border="0" width="680"
                       style="max-width: 680px; min-width: 320px;">
                    <tr>
                        <td valign="top">
                            <?= $this->render("_order-grid", compact("dataProvider", "order")) ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?php } ?>
<?= $this->render("_order_footer") ?>
