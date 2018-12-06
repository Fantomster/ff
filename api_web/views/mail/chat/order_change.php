<?php
/**
 * @var \api_web\modules\integration\classes\documents\OrderContent[] $changed
 * @var string[]                                                      $deleted
 */

$i = 0;
?>
<?php if (!empty($changed)): ?>
    <table class="table">
        <thead>
        <tr>
            <th class="order">#</th>
            <th class="name">Товар</th>
            <th class="article">Артикул</th>
            <th class="quantity">Кол-во</th>
            <th class="price">Цена</th>
            <th class="sum">Сумма</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($changed as $model): $model->refresh(); ?>
            <tr>
                <td class="order"><?= ++$i ?></td>
                <td class="name"><?= $model->product_name ?></td>
                <td class="article"><?= $model->article ?></td>
                <td class="quantity"><?= $model->quantity ?></td>
                <td class="price">
                    <?= \api_web\helpers\CurrencyHelper::asDecimal($model->price) ?>
                    <?= $model->getCurrency()->symbol ?><?= $model->product->ed ? '/' . $model->product->ed : '' ?>
                </td>
                <td class="sum">
                    <?= \api_web\helpers\CurrencyHelper::asDecimal($model->quantity * $model->price) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if (!empty($deleted)): ?>
    <ul>
        <li><?= \Yii::t('api_web', 'order.delete.content') ?>
            <ul>
                <?php foreach ($deleted as $name): ?>
                    <li><?= $name ?></li>
                <?php endforeach; ?>
            </ul>
        </li>
    </ul>
<?php endif; ?>