<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="guid_block_title">
    <p style="border-bottom: 1px dotted #ccc;">
        <?php if (Yii::$app->user->identity->role_id != \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR): ?>
        <span class="title">
            <?=
            Html::a($model->name, ['/order/edit-guide', 'id' => $model->id], [
                'class' => 'link-edit-guide',
            ])
            ?>
        </span>
        <?= Html::tag(
            'i',
            '',
            [
                'class'      => 'fa fa-edit pull-right text-warning rename-template',
                'style'      => 'cursor:pointer',
                'title'      => Yii::t('app', 'frontend.views.order.guides.rename_five', ['ru' => 'Переименовать']),
                'data-id'    => $model->id,
                'data-title' => $model->name
            ]
        ); ?>
        <?php else: ?>
            <span class="title">
            <?= $model->name ?>
        </span>
        <?php endif; ?>
    </p>
</div>
<div class="guid_block_counts">
    <p><?= Yii::t('message', 'frontend.views.order.guides.quantity', ['ru' => 'Кол-во товаров:']) ?>
        <span><?= $model->productCount ?></span></p>
</div>
<div class="guid_block_updated">
    <p><?= Yii::t('message', 'frontend.views.order.guides.changed', ['ru' => 'Изменен:']) ?>
        <span><?= Yii::$app->formatter->asDatetime($model->updated_at, "php:j M Y") ?></span></p>
</div>
<div class="guid_block_buttons">
    <?php if (Yii::$app->user->identity->role_id != \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR): ?>
        <?=
        Html::button('<i class="fa fa-trash"></i> ' . Yii::t('message', 'frontend.views.order.guides.remove', ['ru' => 'Удалить']), [
            'class'    => 'btn btn-sm btn-outline-danger delete-guide',
            'data-url' => Url::to(['/order/ajax-delete-guide', 'id' => $model->id]),
        ])
        ?>
        <?=
        Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('message', 'frontend.views.order.guides.edit', ['ru' => 'Редактировать']), ['/order/edit-guide', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-outline-default',
        ])
        ?>
    <?php endif; ?>
    <?=
    Html::a('<i class="fa fa-shopping-cart"></i> ' . Yii::t('message', 'frontend.views.order.guides.in_basket', ['ru' => 'В корзину']) . ' ', ['order/ajax-show-guide', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-success',
        'data'  => [
            'target'   => '#guideModal',
            'toggle'   => 'modal',
            'backdrop' => 'static',
        ]
    ]);
    ?>
</div>