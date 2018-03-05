<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class="block_right_wrap">
    <p><?= Yii::t('app', 'Итого:') ?> <span id="orderTotal<?= $order->id ?>"><?= $order->total_price ?></span> <?= $currencySymbol ?></p>
</div>
<div class="block_right_wrap_1">
    <?php if ($forMinOrderPrice) { ?>
        <button type="button" class="btn btn-default alButton" disabled="disabled"><?= Yii::t('message', 'frontend.views.order.make_order_two', ['ru' => 'Оформить заказ']) ?></button>
        <p class="alP"><?= Yii::t('message', 'frontend.views.order.until_min', ['ru' => 'до минимального заказа']) ?></p><p><?= $forMinOrderPrice ?> <?= $currencySymbol ?></p>
        <?php } elseif ($forFreeDelivery > 0) { ?>
        <p><?= Yii::t('message', 'frontend.views.order.until_free', ['ru' => 'до бесплатной доставки']) ?> </p><p> <?= $currencySymbol ?></p>
    <?php } elseif ($forFreeDelivery == 0) { ?>
        <p><?= Yii::t('message', 'frontend.views.order.free_delivery', ['ru' => 'бесплатная доставка!']) ?></p>
    <?php } else { ?>
        <p><?= Yii::t('app', 'включая доставку') ?></p><p><?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
    <?php } ?>
    <?=
    (!$forMinOrderPrice) ? Html::button(Yii::t('message', 'frontend.views.order.make_order_two', ['ru' => 'Оформить заказ']), [
                'class' => 'create',
                'data' => [
                    'url' => Url::to(['/order/ajax-make-order']),
                    'id' => $order->id,
                    'all' => false
                ],
            ]) : '';
    ?>
</div>