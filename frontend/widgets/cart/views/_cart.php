<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$count = count($orders);
?>
<div class="maska1"></div>
<div class="block_right_basket">
    <?php Pjax::begin(['enablePushState' => false, 'id' => 'side-cart', 'timeout' => 10000, 'clientOptions' => ['url' => '/order/pjax-cart']]); ?>
    <?php if ($count) { ?>
        <div class="block_pus">
            <div class="block_baasket_head">
                <?= Html::a('Корзина', ['order/checkout'], ['class' => 'a_basket', 'data-pjax' => 0]) ?>
                <span class="col_vo"><?= $count ?></span>
                <img class="hide_basket" src="<?= $baseUrl ?>/img/bask_del1.png" alt="">
            </div>
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

                        <p class="name_tovar"><span class="count"><?= $position->quantity + 0 ?></span><?= $position->product_name ?></p>
                        <p class="name_tovar1"><span class="count"><?= $position->price ?> руб. за 1 <?= $unit ?></span>на общую сумму <span><?= $position->quantity * $position->price ?> руб.</span></p>
                        <?=
                        Html::a('<img class="delete_tovar" src="' . $baseUrl . '/img/tovar_delete.png" alt="">', "#", [
                            'data-url' => Url::to(['/order/ajax-remove-position', 'vendor_id' => $order->vendor_id, 'product_id' => $position->product_id]),
                            'class' => 'cart-delete-position',
                        ])
                        ?>
                    </div>
                <?php } ?>
                <div class="block_sum">
                    <p class="name_s">Сумма</p><p class="count_s"><?= $order->total_price ?> руб</p>
                    <p class="min_zakaz">до минимального(или бесплатной доставки) заказа <br><span>15 000 руб</span></p>
                    <p class="dost_min">включая доставку<br><span>2333 руб</span></p>
                </div>
            <?php } ?>
            <?= Html::a('Оформить заказ', ['order/checkout'], ['class' => 'btn but_zakaz_bask', 'data-pjax' => 0]) ?>
        </div>
    <?php } else { ?>
        <div class="block_wrap_dont_tovar">
            <p class = "block_wrap_dont_tovar_name">В вашей корзине нет товаров.</p>
            <p class = "block_wrap_dont_tovar_p">Ваша корзина пуста и это нужно исправить!<br>
                Ищите кнопки <span>&laquo;Купить&raquo;</span> на нашем сайте. Они рядом  с товарами, которые вы хотите приобрести.<br><br>Вы можете начать с выбора товаров в нашем <?= Html::a('каталоге', ['/order/create'], ['data-pjax' => 0]) ?>
            </p>
        </div>
    <?php } ?>
    <?php Pjax::end(); ?>
</div>