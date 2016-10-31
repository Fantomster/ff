<?php

use common\models\Order;
use common\models\Organization;
?>
<p class="text-left m-b-sm"><b>Дата создания заказа:</b><br>
    <?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
<p class="text-left m-b-sm"><b>Стоимость доставки:</b><br>
    <?= $order->vendor->delivery->delivery_charge ?></p>
<p class="text-left m-b-sm"><b>Стоимость заказа:</b><br>
    <?= $order->total_price ?></p>
<div>
    <?php
    switch ($order->status) {
        case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
            ?>
    <a href="#" class="btn btn-outline-danger btnOrderAction" data-action="cancel"><i class="icon fa fa-ban"></i> Отменить</a>
            <?php if ($organizationType == Organization::TYPE_RESTAURANT) { ?>
                <a href="#" class="btn btn-warning disabled"><span class='badge'><i class="icon fa fa-info"></i></span>&nbsp; Ожидаем подтверждения</a>   
            <?php } else { ?>
                <a href="#" class="btn btn-outline-processing btnOrderAction" data-action="confirm"><i class="icon fa fa-thumbs-o-up"></i> Подтвердить</a>       
            <?php
            }
            break;
        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
            ?>
            <a href="#" class="btn btn-outline-danger btnOrderAction" data-action="cancel"><i class="icon fa fa-ban"></i> Отменить</a>       
            <?php if ($organizationType == Organization::TYPE_SUPPLIER) { ?>
                <a href="#" class="btn btn-warning disabled"><span class='badge'><i class="icon fa fa-info"></i></span>&nbsp; Ожидаем подтверждения</a>   
            <?php } else { ?>
                <a href="#" class="btn btn-outline-processing btnOrderAction" data-action="confirm"><i class="icon fa fa-thumbs-o-up"></i> Подтвердить</a>       
            <?php
            }
            break;
        case Order::STATUS_PROCESSING:
            ?>
            <a href="#" class="btn btn-outline-danger btnOrderAction" data-action="cancel"><i class="icon fa fa-ban"></i> Отменить</a>       
            <?php if ($organizationType == Organization::TYPE_SUPPLIER) { ?>
                <a href="#" class="btn btn-processing disabled"><span class='badge'><i class="icon fa fa-info"></i></span>&nbsp; Исполняется</a>  
            <?php } else { ?>
                <a href="#" class="btn btn-outline-success btnOrderAction" data-action="confirm"><i class="icon fa fa-check"></i> Получить</a> 
            <?php
            }
            break;
        case Order::STATUS_DONE;
            ?>
            <a href="#" class="btn btn-success disabled"><span class='badge'><i class="icon fa fa-info"></i></span>&nbsp; Выполнен</a>       
            <?php
            break;
        case Order::STATUS_REJECTED;
            ?>
            <a href="#" class="btn btn-danger disabled"><span class='badge'><i class="icon fa fa-info"></i></span>&nbsp; Отклонен</a>       
            <?php
            break;
        case Order::STATUS_CANCELLED;
            ?>
            <a href="#" class="btn btn-danger disabled"><span class='badge'><i class="icon fa fa-info"></i></span>&nbsp; Отменен</a>       
        <?php
        break;
}
?>
            <a href="#" class="btn btn-outline-default" id="btnPrint"><i class="icon fa fa-print"></i> Распечатать</a>
</div>