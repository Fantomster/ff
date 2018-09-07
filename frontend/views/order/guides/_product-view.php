<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var $model array */
/** @var $guideProductList array */

$messProductPrice = Yii::t('app', 'common.models.price', ['ru' => 'Цена']);
$messProductED = Yii::t('app', $model["ed"]);
$messProductExists = Yii::t('message', 'frontend.views.order.guides.good_added', ['ru' => 'Продукт добавлен']);
$messProductAdd = Yii::t('message', 'frontend.views.order.guides.add_in_template', ['ru' => 'Добавить в шаблон']);

?>
<td>
    <div class="guid_block_create_title">
        <p><?= $model["product"] ?></p>
    </div>
    <div class="guid_block_create_counts">
        <p><?= $messProductPrice ?>: <span><?= $model['price'] . " " . $model["symbol"] . "/" . $messProductED ?></span>
        </p>
    </div>
</td>
<td>
    <?php
    if (in_array($model["id"], $guideProductList)) {
        echo Html::button('<i class="fa fa-thumbs-o-up"></i> ' . $messProductExists, [
            'class' => 'btn btn-md btn-gray pull-right disabled in-guide',
            'id' => 'product' . $model['id'],
            'data-url' => Url::to(['/order/ajax-add-to-guide', 'id' => $model["id"]]),
        ]);
    } else {
        echo Html::button('<i class="fa fa-plus"></i> ' . $messProductAdd . ' ', [
            'class' => 'btn btn-md btn-success pull-right add-to-guide',
            'id' => 'product' . $model['id'],
            'data-url' => Url::to(['/order/ajax-add-to-guide', 'id' => $model["id"]]),
        ]);
    }
    ?>
</td>
