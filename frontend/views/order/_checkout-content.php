<?php

use kartik\widgets\TouchSpin;
use yii\helpers\Html;
use yii\helpers\Url;

foreach ($content as $position) {
    $note = $position->getNote();
    ?>
    <div class="block_left_bot">
        <div class="block_left_bot_left">
            <?php //<img class= "img_product" src="<?= $position->product->imageUrl ? >" alt=""> ?>
            <p class = "block_left_bot_left_name"><?= Html::decode(Html::decode($position->product_name)) ?></p>
            <p class = "block_left_bot_left_art">Артикул: <?= $position->product->article ?></p><br>
            <p class = "kr_p">Кратность: <?= $position->product->units ? $position->product->units : '' ?><?= $position->product->ed ?></p>
            <?=
            Html::button('Комментарий к товару', [
                'class' => 'add-note but_com',
                'data' => [
                    'id' => $position->product_id,
                    'url' => Url::to(['order/ajax-set-note', 'product_id' => $position->product_id]),
                    'toggle' => "tooltip",
                    'placement' => "bottom",
                    'original-title' => isset($note) ? $note->note : '',
                ],
            ])
            ?>
            <br><br>
        </div>
        <div class="block_chek_kolvo">
            <?=
            TouchSpin::widget([
                'name' => "OrderContent[" . $position->id . "][quantity]",
                'pluginOptions' => [
                    'initval' => $position->quantity,
                    'min' => (isset($position->units) && ($position->units)) ? $position->units : 0.001,
                    'max' => PHP_INT_MAX,
                    'step' => (isset($position->units) && ($position->units)) ? $position->units : 1,
                    'decimals' => (empty($position->units) || (fmod($position->units, 1) > 0)) ? 3 : 0,
                    'forcestepdivisibility' => (isset($position->units) && ($position->units && (floor($position->units) == $position->units))) ? 'floor' : 'none',
                    'buttonup_class' => 'btn btn-default',
                    'buttondown_class' => 'btn btn-default',
                    'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                    'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                ],
                'options' => ['class' => 'quantity form-control '],
            ]) . Html::hiddenInput("OrderContent[$position->id][id]", $position->id)
            ?>
        </div>
        <div class="block_cena">
            <p class = "block_cena_p"><span id="total<?= $position->id ?>"><?= number_format($position->price * $position->quantity, 2) ?></span> руб.</p>
            <p class = "block_cena_p1"><?= $position->quantity ?> x <span> <?= $position->price ?> руб.</span></p>
            <?=
            Html::a('<img class= "delete_tovar1" src="/img/tovar_delete.png" alt="">', '#', [
                'class' => 'remove',
                'data-url' => Url::to(['/order/ajax-remove-position', 'vendor_id' => $vendor_id, 'product_id' => $position->product_id]),
            ])
            ?>
        </div>
    </div>
<?php } ?>