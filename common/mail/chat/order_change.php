<?php

use \api_web\helpers\CurrencyHelper;
use common\models\Order;

/**
 * @var \api_web\modules\integration\classes\documents\OrderContent[] $changed
 * @var \api_web\modules\integration\classes\documents\OrderContent[] $deleted
 */
?>
<p><?= Yii::t('app', 'Изменились детали заказа') ?>:</p>
<?php if (!empty($changed) || !empty($deleted)): ?>
    <table class="table">
        <thead>
        <tr>
            <th class="main-action"></th>
            <th class="order">#</th>
            <th class="name"><?= \Yii::t('api_web', 'mail.chat.order_changed.name') ?></th>
            <th class="article"><?= \Yii::t('api_web', 'mail.chat.order_changed.article') ?></th>
            <th class="quantity"><?= \Yii::t('api_web', 'mail.chat.order_changed.quantity') ?></th>
            <th class="quantity-action"></th>
            <th class="price"><?= \Yii::t('api_web', 'mail.chat.order_changed.price') ?></th>
            <th class="price-action"></th>
            <th class="sum"><?= \Yii::t('api_web', 'mail.chat.order_changed.sum') ?></th>
        </tr>
        </thead>
        <tbody>

        <?php if (!empty($changed)): $i = 0; ?>
            <?php foreach ($changed as $model) : $new = $model->isNewRecord; ?>
                <?php
                if ($new) {
                    $model->refresh();
                }
                $oldQuantity = $model->getOldAttribute('quantity');
                $oldPrice = $model->getOldAttribute('price');
                ?>
                <tr class="<?= $new ? 'action-added' : 'action-changed' ?>">
                    <td class="main-action <?= $new ? 'action-added' : 'action-changed' ?>"><i
                                class="material-icons"></i></td>
                    <td class="order"><?= ++$i ?></td>
                    <td class="name"><?= $model->product_name ?></td>
                    <td class="article"><?= $model->article ?></td>
                    <td class="quantity <?= $model->getCssClassChatMessage('quantity') ?>"><?= number_format($model->quantity, 3, '.', '') ?>
                        <?php if ($model->quantity != $oldQuantity): ?>
                            <p class="value-old">
                                <?= number_format($oldQuantity, 3, '.', '') ?>
                            </p>
                        <?php endif; ?>
                    </td>
                    <td class="quantity-action <?= $model->getCssClassChatMessage('quantity') ?>"><i
                                class="material-icons"></i></td>
                    <td class="price <?= $model->getCssClassChatMessage('price') ?>">
                        <?= CurrencyHelper::asDecimal($model->price) ?>
                        <?= $model->getCurrency()->symbol ?>
                        <?= $model->product->ed ? '/' . $model->product->ed : '' ?>
                        <?php if ($model->price != $oldPrice): ?>
                            <p class="value-old">
                                <?= CurrencyHelper::asDecimal($oldPrice) ?>
                            </p>
                        <?php endif; ?>
                    </td>
                    <td class="price-action <?= $model->getCssClassChatMessage('price') ?>"><i
                                class="material-icons"></i></td>
                    <td class="sum">
                        <?= CurrencyHelper::asDecimal($model->quantity * $model->price) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($deleted)): $i = 0; ?>
            <?php foreach ($deleted as $model): ?>
                <tr class="action-removed">
                    <td class="main-action"><i class="material-icons"></i></td>
                    <td class="order"><?= ++$i ?></td>
                    <td class="name"><?= $model->product_name ?></td>
                    <td class="article"><?= $model->article ?></td>
                    <td class="quantity"><?= number_format($model->quantity, 3, '.', '') ?></td>
                    <td class="quantity-action"><i class="material-icons"></i></td>
                    <td class="price <?= $model->getCssClassChatMessage('price') ?>">
                        <?= CurrencyHelper::asDecimal($model->price) ?>
                        <?= $model->getCurrency()->symbol ?>
                        <?= $model->product->ed ? '/' . $model->product->ed : '' ?>
                    </td>
                    <td class="price-action"><i class="material-icons"></i></td>
                    <td class="sum">
                        <?= CurrencyHelper::asDecimal($model->quantity * $model->price) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        </tbody>
    </table>
<?php endif; ?>

<?php if (!empty($additionalParams)): $i = 0; ?>
    <?php foreach ($additionalParams as $key => $param): ?>
        <?php if ($key == 'discount_type') {
            $newValue = ($param['value'] == Order::DISCOUNT_FIXED) ? "RUB" : "%";
            $oldValue = ($param['old_value'] == Order::DISCOUNT_FIXED) ? "RUB" : "%";
        } else {
            $newValue = $param['value'];
            $oldValue = $param['old_value'];
        }
        ?>
        <p><?= $param['name'] ?> : <?= $newValue ?> <span class="value-old"><?= $oldValue ?></span></p>
    <?php endforeach; ?>
<?php endif; ?>
