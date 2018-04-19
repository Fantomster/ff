<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<p class = "block_cena_p"><span id="total<?= $position['id']?>"><?= number_format($position['price'] * $position['in_basket'], 2) ?></span> <?= $position['currency'] ?></p>
<p class = "block_cena_p1"><?= $position['in_basket'] ?> x <span> <?= $position['price'] ?> <?= $position['currency'] ?></span></p>
<?=
Html::a('<img class= "delete_tovar1" src="/img/tovar_delete.png" alt="">', '#', [
    'class' => 'remove',
    'data-url' => Url::to(['/order/ajax-remove-position', 'product_id' => $position['id']]),
])
?>
