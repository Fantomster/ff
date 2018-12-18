<?php
/**
 * @var \api_web\modules\integration\classes\documents\OrderContent[] $changed
 * @var string[]                                                      $deleted
 */

$i = 0;
$trClass = "";
?>
<?php if (!empty($changed) || !empty($deleted)): ?>
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
        <?php if (!empty($changed)): ?>
            <?php foreach ($changed as $model) :
                $trClass = "";
                if($model->isNewRecord)
                    {
                        $model->refresh();
                        $trClass = "class=\"action-added\"";
                    }
                ?>
                <tr <?= $trClass ?>>
                    <td class="order"><?= ++$i ?></td>
                    <td class="name"><?= $model->product_name ?></td>
                    <td class="article"><?= $model->article ?></td>
                    <td class="quantity<?= ($model->quantity != $model->oldAttributes['quantity']) ? (($model->quantity > $model->oldAttributes['quantity']) ? " action-raised" : " action-lowered" ) : ""?>"><?= $model->quantity ?></td>
                    <td class="price<?= ($model->price != $model->oldAttributes['price']) ? (($model->price > $model->oldAttributes['price']) ? " action-raised" : " action-lowered" ) : ""?>">
                        <?= \api_web\helpers\CurrencyHelper::asDecimal($model->price) ?>
                        <?= $model->getCurrency()->symbol ?><?= $model->product->ed ? '/' . $model->product->ed : '' ?>
                    </td>
                    <td class="sum">
                        <?= \api_web\helpers\CurrencyHelper::asDecimal($model->quantity * $model->price) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (!empty($deleted)): ?>
            <?php
            $i = 0;
            ?>
                <?php foreach ($deleted as $model): $model->refresh(); ?>
                    <tr class = "action-removed">
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