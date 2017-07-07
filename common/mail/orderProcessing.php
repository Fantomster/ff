<?php
use yii\helpers\Url;
use common\models\Organization;

$orgType = ($senderOrg->type_id == Organization::TYPE_RESTAURANT) ? "Ресторан" : "Поставщик";
?>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
    <?= $orgType . ' ' . $senderOrg->name . ' подтвердил заказ №' . $order->id ?>.
    Заказ находится в стадии выполнения.
</p>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
    Для просмотра деталей пройдите по ссылке:
</p>
<br style="margin: 0; padding: 0;" />
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <a href="<?= Url::toRoute(["/order/view", "id" => $order->id], true); ?>" 
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
    width: 80%;">Заказ №<?= $order->id ?></a>
</div>
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <?= $this->render('_bill', compact('order', 'dataProvider')) ?>
</div>