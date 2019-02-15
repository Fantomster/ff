<?php

use yii\helpers\Url;
use yii\helpers\Html;
use common\models\User;
use common\models\Role;

?>

<div class="block_right_wrap">
    <p><?= Yii::t('app', 'Итого:') ?> <span
                id="orderTotal<?= $cart['id'] ?>"><?= $cart['total_price'] ?></span> <?= $cart['currency'] ?></p>
</div>
<div class="block_right_wrap_1">
    <?php if ($cart['for_min_cart_price']) { ?>
        <?php
        $user_id = Yii::$app->user->id;
        $role_id = User::find()->select('role_id')->where(['id' => $user_id])->column();
        if ($role_id[0] != Role::ROLE_RESTAURANT_ORDER_INITIATOR) { ?>
            <button type="button" class="btn btn-default alButton"
                    disabled="disabled"><?= Yii::t('message', 'frontend.views.order.make_order_two', ['ru' => 'Оформить заказ']) ?></button>
        <?php } ?>
        <p class="alP"><?= Yii::t('message', 'frontend.views.order.until_min', ['ru' => 'до минимального заказа']) ?></p>
        <p><?= $cart['for_min_cart_price'] ?> <?= $cart['currency'] ?></p>
    <?php } elseif ($cart['for_free_delivery'] > 0) { ?>
        <p><?= Yii::t('message', 'frontend.views.order.until_free', ['ru' => 'до бесплатной доставки']) ?> </p>
        <p><?= $cart['for_free_delivery'] ?> <?= $cart['currency'] ?></p>
    <?php } elseif ($cart['for_free_delivery'] == 0) { ?>
        <p><?= Yii::t('message', 'frontend.views.order.free_delivery', ['ru' => 'бесплатная доставка!']) ?></p>
    <?php } else { ?>
        <p><?= Yii::t('app', 'включая доставку') ?></p><p><?= $cart['delivery_price'] ?> <?= $cart['currency'] ?></p>
    <?php } ?>
    <?php
    $user_id = Yii::$app->user->id;
    $role_id = User::find()->select('role_id')->where(['id' => $user_id])->column();
    if ($role_id[0] != Role::ROLE_RESTAURANT_ORDER_INITIATOR) {
        echo (!$cart['for_min_cart_price']) ? Html::button(Yii::t('message', 'frontend.views.order.make_order_two', ['ru' => 'Оформить заказ']), [
            'class' => 'create',
            'data'  => [
                'url' => Url::to(['/order/ajax-make-order']),
                'id'  => $cart['id'],
                'all' => false
            ],
        ]) : '';
    }
    ?>
</div>