<?php

use yii\helpers\Url;
use common\models\Organization;

$orgType = ($senderOrg->type_id == Organization::TYPE_RESTAURANT) ? Yii::t('app', 'common.mail.order_created.rest', ['ru'=>"Ресторан"]) : Yii::t('app', 'common.mail.order_created.vendor', ['ru'=>"Поставщик"]);
?>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
<?= $orgType . ' ' . $senderOrg->name . Yii::t('app', 'common.mail.order_created.new_order', ['ru'=>' создал новый заказ №']) . $order->id ?>
</p>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('app', 'common.mail.order_created.manager', ['ru'=>'Менеджер']) ?>: <?= $order->createdByProfile->full_name ?>
</p>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('app', 'common.mail.order_created.link_for_details', ['ru'=>'Для просмотра деталей пройдите по ссылке']) ?>:
</p>
<br style="margin: 0; padding: 0;" />
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <a href="<?= $order->getUrlForUser($recipient) ?>"
       style="text-decoration: none;
       color: #FFF;
       background-color: #84bf76;
       padding: 10px 16px;
       font-weight: bold;
       margin-right: 10px;
       text-align: center;
       cursor: pointer;
       display: inline-block;
       border-radius: 4px;
       width: 80%;"><?= Yii::t('app', 'common.mail.order_created.order_no', ['ru'=>'Заказ №']) ?><?= $order->id ?></a>
</div>
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <?= $this->render('_bill', compact('order', 'dataProvider')) ?>
</div>