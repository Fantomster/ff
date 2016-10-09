<?php
use common\models\Order;
use common\models\Organization;

switch ($order->status) {
    case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR: 
?>
<a href="#" class="btn btn-danger btnOrderAction" data-action="cancel">Отменить</a>
<?php if ($organizationType == Organization::TYPE_RESTAURANT) { ?>
<a href="#" class="btn btn-default disabled">Ожидаем подтверждения заказа</a>   
<?php } else { ?>
<a href="#" class="btn btn-primary btnOrderAction" data-action="confirm">Подтвердить</a>       
<?php }
        break;
    case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
?>
<a href="#" class="btn btn-danger btnOrderAction" data-action="cancel">Отменить</a>       
<?php if ($organizationType == Organization::TYPE_SUPPLIER) { ?>
<a href="#" class="btn btn-default disabled">Ожидаем подтверждения заказа</a>   
<?php } else { ?>
<a href="#" class="btn btn-primary btnOrderAction" data-action="confirm">Подтвердить</a>       
<?php }  
        break;
    case Order::STATUS_PROCESSING:
?>
<a href="#" class="btn btn-danger btnOrderAction" data-action="cancel">Отменить</a>       
<?php if ($organizationType == Organization::TYPE_SUPPLIER) { ?>
<a href="#" class="btn btn-default disabled">Исполняется</a>  
<?php } else { ?>
<a href="#" class="btn btn-primary btnOrderAction" data-action="confirm">Получен</a> 
<?php }  
        break;
    case Order::STATUS_DONE;
?>
<a href="#" class="btn btn-success disabled">Выполнен</a>       
<?php   
        break;
    case Order::STATUS_REJECTED;
?>
<a href="#" class="btn btn-danger disabled">Отклонен</a>       
<?php   
        break;
    case Order::STATUS_CANCELLED;
?>
<a href="#" class="btn btn-danger disabled">Отменен</a>       
<?php   
        break;
}