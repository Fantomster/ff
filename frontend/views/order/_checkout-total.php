<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class="block_right_wrap">
    <p><?= Yii::t('app', 'Итого:') ?> <span id="orderTotal<?= $order->id ?>"><?= $cart['total_price'] ?></span> <?= $currencySymbol ?></p>
</div>
<div class="block_right_wrap_1">
    <?php if ($cart['for_min_order_price']) { ?>
        <button type="button" class="btn btn-default alButton" disabled="disabled"><?= Yii::t('message', 'frontend.views.order.make_order_two', ['ru' => 'Оформить заказ']) ?></button>
        <p class="alP"><?= Yii::t('message', 'frontend.views.order.until_min', ['ru' => 'до минимального заказа']) ?></p><p><?= $cart['for_min_order_price'] ?> <?= $cart['currency'] ?></p>
        <?php } elseif ($cart['for_free_delivery'] > 0) { ?>
        <p><?= Yii::t('message', 'frontend.views.order.until_free', ['ru' => 'до бесплатной доставки']) ?> </p><p> <?= $cart['currency'] ?></p>
    <?php } elseif ($cart['for_free_delivery'] == 0) { ?>
        <p><?= Yii::t('message', 'frontend.views.order.free_delivery', ['ru' => 'бесплатная доставка!']) ?></p>
    <?php } else { ?>
        <p><?= Yii::t('app', 'включая доставку') ?></p><p><?= $cart['delivery_price'] ?> <?= $cart['currency'] ?></p>
    <?php } ?>
    <?=
    (!$cart['for_min_order_price']) ? Html::button(Yii::t('message', 'frontend.views.order.make_order_two', ['ru' => 'Оформить заказ']), [
                'class' => 'create',
                'data' => [
                    'url' => Url::to(['/order/ajax-make-order']),
                    'id' => $cart['id'],
                    'all' => false
                ],
            ]) : '';
    ?>
</div>