<?php

use yii\helpers\Html;
?>
<div class="block_wrap_zap">
    <div class="block_wrap_name">
        <?php
        if ($model->vendor->hasActiveUsers()) {
            echo Html::a($model->vendor->name, ['client/view-catalog', 'id' => $model->cat_id], [
                'class' => 'redact',
                'style' => 'text-center',
                'data-pjax' => 0,
                'data' => [
                    'target' => '#view-catalog',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                ],
            ]);
        } else {
            echo Html::a($model->vendor->name, ['client/edit-catalog', 'id' => $model->cat_id], [
                'class' => 'redact',
                'style' => 'text-center',
                'data-pjax' => 0,
                'data' => [
                    'target' => '#edit-catalog',
                    'toggle' => 'modal',
                    'backdrop' => 'static',]
            ]);
        }
        ?>
    </div>
    <div class="block_wrap_d">
        <?php
        if ($model->vendor->hasActiveUsers()) {
            echo Html::a('<img src="/img/redact_icon.png" alt="">Просмотреть продукты', ['client/view-catalog', 'id' => $model->cat_id], [
                'class' => 'redact',
                'style' => 'text-center',
                'data-pjax' => 0,
                'data' => [
                    'target' => '#view-catalog',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                ],
            ]);
        } else {
            echo Html::a('<img src="/img/redact_icon.png" alt="">Редактировать продукты', ['client/edit-catalog', 'id' => $model->cat_id], [
                'class' => 'redact',
                'style' => 'text-center',
                'data-pjax' => 0,
                'data' => [
                    'target' => '#edit-catalog',
                    'toggle' => 'modal',
                    'backdrop' => 'static',]
            ]);
        }
        ?>
        <?=
        Html::a('<img src="/img/corzina_icon.png" alt="">Удалить', '#', [
            'class' => 'delete-vendor del',
            'data' => ['id' => $model->supp_org_id],
        ]);
        ?>
    </div>
</div><br>