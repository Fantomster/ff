<?php

use yii\helpers\Html;
?>
<div class="list-group">
    <?php
    foreach ($orders as $order) {
        echo Html::a("Заказ у $order[vendor_name] &nbsp;<span class='badge'>" . count($order['content']) . "</span>", ['order/ajax-show-order', 'vendor_id' => $order['vendor_id']], [
            '',
            'class' => 'list-group-item show-order',
            'data-id' => $order['vendor_id'],
            'data' => [
                'target' => '#showOrder',
                'toggle' => 'modal',
                'backdrop' => 'static',
            ]
        ]);
        //<li>Заказ у <?=$order['vendor_name']? > (<?=count($order['content'])? >)
    }
    ?>
</div>