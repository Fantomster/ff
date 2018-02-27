<?php

use kartik\widgets\TouchSpin;
use yii\helpers\Html;
use yii\helpers\Url;

$currencySymbol = $order->currency->symbol;
$content = $order->orderContent;
$vendor_id = $order->vendor_id;

foreach ($content as $position) {
    $note = $position->comment;
    ?>
    <div class="block_left_bot">
        <div class="block_left_bot_left">
            <?php //<img class= "img_product" src="<?= $position->product->imageUrl ? >" alt=""> ?>
            <p class = "block_left_bot_left_name"><?= Html::decode(Html::decode($position->product_name)) ?></p>
            <p class = "block_left_bot_left_art"><?= Yii::t('message', 'frontend.views.order.art_four', ['ru'=>'Артикул:']) ?> <?= $position->product->article ?></p><br>
            <p class = "kr_p"><?= Yii::t('message', 'frontend.views.order.frequency_three', ['ru'=>'Кратность:']) ?> <?= $position->product->units ? $position->product->units : '' ?><?= Yii::t('app', $position->product->ed) ?></p>
            <?=
            Html::button(Yii::t('message', 'frontend.views.order.good_comment_three', ['ru'=>'Комментарий к товару']), [
                'class' => 'add-note but_com',
                'data' => [
                    'id' => $position->product_id,
                    'url' => Url::to(['order/ajax-set-note', 'order_content_id' => $position->id]),
                    'toggle' => "tooltip",
                    'placement' => "bottom",
                    'original-title' => isset($position->comment) ? $position->comment : '',
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
        <div class="block_cena" id="position_<?= $position->id ?>">
            <?= $this->render("_checkout-position-price", compact("position", "currencySymbol", "vendor_id")) ?>
        </div>
    </div>
<?php } ?>