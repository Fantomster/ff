<div class = "block_wrapper">
    <div class = "wrapppp">
        <?=
        $canRepeatOrder ? Html::button('Повторить заказ', [
                    'class' => 'but1',
                    'data-url' => Url::to(['order/repeat', 'id' => $order->id]),
                ]) : ""
        ?>
        <button class = "but2">Печать</button><br><br>
        <p class = "ppp" >Общая сумма</p>

        <p class = "pppp"><?= $order->total_price ?><span> руб</span></p><br>
        <?php
        $deliveryCost = $order->calculateDelivery();
        if ($deliveryCost) {
            ?>
            <p class = "ps">включая доставку</p>
            <p class = "ps"><?= $deliveryCost ?> руб</p>
        <?php } else { ?>
            <p class = "ps">&nbsp;</p>
            <p class = "ps">&nbsp;</p>
        <?php } ?>
        <p class = "ps">дата создания </p>
        <p class = "ps"><?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
        <div class="row">
            <div class="col-md-12"><?= $actionButtons ?></div>
        </div>
        <!--    <button class = "but3">Отменить</button>
            <button class = "but4">Подтвердить</button><br><br>-->
    </div>
</div>