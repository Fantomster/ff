<?php

use \api_web\helpers\CurrencyHelper;

/**
 * @var \api_web\modules\integration\classes\documents\OrderContent[] $changed
 * @var \api_web\modules\integration\classes\documents\OrderContent[] $deleted
 */

?>
<?php if (!empty($changed) || !empty($deleted)): ?>
    <table class="table">
        <thead>
        <tr>
            <th class="order">#</th>
            <th class="name"><?= \Yii::t('api_web', 'mail.chat.order_changed.name') ?></th>
            <th class="article"><?= \Yii::t('api_web', 'mail.chat.order_changed.article') ?></th>
            <th class="quantity"><?= \Yii::t('api_web', 'mail.chat.order_changed.quantity') ?></th>
            <th class="price"><?= \Yii::t('api_web', 'mail.chat.order_changed.price') ?></th>
            <th class="sum"><?= \Yii::t('api_web', 'mail.chat.order_changed.sum') ?></th>
        </tr>
        </thead>
        <tbody>

        <?php if (!empty($changed)): $i = 0; ?>
            <?php foreach ($changed as $model) : $new = $model->isNewRecord; ?>
                <?php if ($new) {
                    $model->refresh();
                } ?>
                <tr class="<?= $new ? 'action-added' : 'action-changed' ?>">
                    <td class="order"><?= ++$i ?></td>
                    <td class="name"><?= $model->product_name ?></td>
                    <td class="article"><?= $model->article ?></td>
                    <td class="quantity <?= $model->getCssClassChatMessage('quantity') ?>"><?= $model->quantity ?></td>
                    <td class="price <?= $model->getCssClassChatMessage('price') ?>">
                        <?= CurrencyHelper::asDecimal($model->price) ?>
                        <?= $model->getCurrency()->symbol ?>
                        <?= $model->product->ed ? '/' . $model->product->ed : '' ?>
                    </td>
                    <td class="sum">
                        <?= CurrencyHelper::asDecimal($model->quantity * $model->price) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($deleted)): ?>
            <?php foreach ($deleted as $model): ?>
                <tr class="action-removed">
                    <td class="order"><?= ++$i ?></td>
                    <td class="name"><?= $model->product_name ?></td>
                    <td class="article"><?= $model->article ?></td>
                    <td class="quantity"><?= $model->quantity ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        </tbody>
    </table>
<?php endif; ?>