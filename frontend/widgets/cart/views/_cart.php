<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$count = count($orders);
?>
<div class="maska1"></div>
<div class="block_right_basket" style="padding-bottom: 50px;">
    <?php Pjax::begin(['enablePushState' => false, 'id' => 'side-cart', 'timeout' => 10000, 'clientOptions' => ['url' => '/order/pjax-cart']]); ?>
    <?php if ($count) { ?>
        <div class="block_pus">
            <div class="block_baasket_head">
                <?= Html::a(Yii::t('message', 'frontend.widgets.cart.views.basket', ['ru'=>'Корзина']), ['order/checkout'], ['class' => 'a_basket', 'data-pjax' => 0]) ?>
                <span class="col_vo"><?= $count ?></span>
                <img class="hide_basket" src="<?= $baseUrl ?>/img/bask_del1.png" alt="">
            </div>
            <?= Html::a(Yii::t('app', 'frontend.widgets.cart.views.go_to', ['ru'=>'Перейти к оформлению']), ['order/checkout'], ['class' => 'btn but_zakaz_bask', 'data-pjax' => 0]) ?>
            <?php
            foreach ($orders as $order) {
                ?>
                <div class="block_name_copmain">
                    <p><?= $order->vendor->name ?></p>
                </div>
                <?php
                foreach ($order->orderContent as $position) {
                    $unit = empty($position->product->ed) ? "" : $position->product->ed;
                    ?>
                    <div class="block_tovar_check">

                        <p class="name_tovar"><span class="count"><?= $position->quantity + 0 ?></span><?= Html::decode(Html::decode($position->product_name)) ?></p>
                        <p class="name_tovar1"><span class="count"><?= $position->price ?> <?= $order->currency->symbol ?> <?= Yii::t('app', 'frontend.widgets.cart.views.for_one', ['ru'=>'за 1']) ?> <?= $unit ?></span><?= Yii::t('app', 'frontend.widgets.cart.views.sum', ['ru'=>'на общую сумму']) ?> <span><?= $position->quantity * $position->price ?> <?= $order->currency->symbol ?></span></p>
                        <?=
                        Html::a('<img class="delete_tovar" src="' . $baseUrl . '/img/tovar_delete.png" alt="">', "#", [
                            'data-url' => Url::to(['/order/ajax-remove-position', 'vendor_id' => $order->vendor_id, 'product_id' => $position->product_id]),
                            'class' => 'cart-delete-position',
                        ])
                        ?>
                    </div>
                <?php } 
                    $forMinOrderPrice = $order->forMinOrderPrice();
                    $forFreeDelivery = $order->forFreeDelivery();
                    $test = 1;
                ?>
                <div class="block_sum">
                    <div class="row">
                        <div class="col-md-4 col-xs-4 name_s"><?= Yii::t('app', 'frontend.widgets.cart.views.sum_two', ['ru'=>'Сумма']) ?></div>
                        <div class="col-md-8 col-xs-8 count_s"><?= $order->total_price ?> <?= $order->currency->symbol ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-xs-6 min_zakaz">
                        <?php if ($forMinOrderPrice) { ?>
                        <?= Yii::t('app', 'frontend.widgets.cart.views.for_minimal', ['ru'=>'до минимального заказа']) ?> <br><span><?= $forMinOrderPrice ?> <?= $order->currency->symbol ?></span>
                        <?php } elseif ($forFreeDelivery > 0) { ?>
                        <?= Yii::t('app', 'frontend.widgets.cart.views.for_free', ['ru'=>'до бесплатной доставки']) ?> <br><span><?= $forFreeDelivery ?> <?= $order->currency->symbol ?></span>
                        <?php } elseif ($forFreeDelivery == 0) { ?>
                        <?= Yii::t('app', 'frontend.widgets.cart.views.free_delivery', ['ru'=>'бесплатная доставка!']) ?><br><span>&nbsp;</span>
                        <?php } ?>
                        </div>
                        <div class="col-md-6 col-xs-6 dost_min"><?= Yii::t('app', 'frontend.widgets.cart.views.incl_delivery', ['ru'=>'включая доставку']) ?><br><span><?= $order->calculateDelivery() ?> <?= $order->currency->symbol ?></span></div>
                    </div>
                </div>
            <?php } ?>
            <?= Html::a('Перейти к оформлению', ['order/checkout'], ['class' => 'btn but_zakaz_bask', 'data-pjax' => 0]) ?>
        </div>
    <?php } else { ?>
        <div class="block_wrap_dont_tovar">
            <p class = "block_wrap_dont_tovar_name"><?= Yii::t('app', 'frontend.widgets.cart.views.no_goods', ['ru'=>'В вашей корзине нет товаров.']) ?></p>
            <p class = "block_wrap_dont_tovar_p"><?= Yii::t('app', 'frontend.widgets.cart.views.empty', ['ru'=>'Ваша корзина пуста и это нужно исправить!']) ?><br>
                <?= Yii::t('app', 'frontend.widgets.cart.views.seek', ['ru'=>'Ищите кнопки <span>&laquo;Купить&raquo;</span> на нашем сайте. Они рядом  с товарами, которые вы хотите приобрести.<br><br>Вы можете начать с выбора товаров в нашем {cat}', 'cat'=>Html::a('каталоге', ['/order/create'], ['data-pjax' => 0])]) ?>
            </p>
        </div>
    <?php } ?>
    <?php Pjax::end(); ?>
</div>