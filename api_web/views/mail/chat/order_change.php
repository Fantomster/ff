<?php

use \api_web\helpers\CurrencyHelper;

/**
 * @var \api_web\modules\integration\classes\documents\OrderContent[] $changed
 * @var \api_web\modules\integration\classes\documents\OrderContent[] $deleted
 */

/**
 * @param \yii\db\ActiveRecord $model
 * @param                      $attribute
 * @return string
 */
function getActionClass($model, $attribute)
{
    $class = '';
    if ($model->getAttribute($attribute) != $model->getOldAttribute($attribute)) {
        if ($model->getAttribute($attribute) > $model->getOldAttribute($attribute)) {
            $class = 'action-raised';
        } else {
            $class = 'action-lowered';
        }
    }
    return $class;
}

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
            <?php foreach ($changed as $model) : ?>
                <?php if ($model->isNewRecord) {
                    $model->refresh();
                    $add = true;
                } else {
                    $add = false;
                } ?>
                <tr class="<?= $add ? 'action-added' : 'action-changed' ?>">
                    <td class="order"><?= ++$i ?></td>
                    <td class="name"><?= $model->product_name ?></td>
                    <td class="article"><?= $model->article ?></td>
                    <td class="quantity <?= getActionClass($model, 'quantity') ?>"><?= $model->quantity ?></td>
                    <td class="price <?= getActionClass($model, 'price') ?>">
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

        <?php if (!empty($deleted)): $i = 0; ?>
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