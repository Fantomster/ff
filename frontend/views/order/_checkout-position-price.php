<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<p class = "block_cena_p"><span id="total<?= $position->id ?>"><?= number_format($position->price * $position->quantity, 2) ?></span> <?= $currencySymbol ?></p>
<p class = "block_cena_p1"><?= $position->quantity ?> x <span> <?= $position->price ?> <?= $currencySymbol ?></span></p>
<?=
Html::a('<img class= "delete_tovar1" src="/img/tovar_delete.png" alt="">', '#', [
    'class' => 'remove',
    'data-url' => Url::to(['/order/ajax-remove-position', 'vendor_id' => $vendor_id, 'product_id' => $position->product_id]),
])
?>
