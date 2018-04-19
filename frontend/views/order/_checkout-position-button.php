<?php

use yii\helpers\Url;
use yii\helpers\Html;

echo (!$cart['for_min_order_price']) ? Html::button(Yii::t('message', 'frontend.views.order.make_order', ['ru' => 'Оформить заказ']), [
            'class' => 'but_go_zakaz create pull-right',
            'data' => [
                'url' => Url::to(['/order/ajax-make-order']),
                'id' => $cart['id'],
                'all' => false,
            ]
        ]) : ('<div class="but_go_zakaz create pull-right alRightBlock"><button type="button" class="btn btn-default" disabled="disabled">' . Yii::t('message', 'frontend.views.order.make_order_two', ['ru' => 'Оформить заказ']) . '</button><br><p>' . Yii::t('message', 'frontend.views.order.until_min', ['ru' => 'до минимального заказа']) . ' ' . $cart['for_min_cart_price'] . ' ' . $cart['currency'] . '</p></div>');
