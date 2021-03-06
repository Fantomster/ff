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
        if ($model->vendor->hasActiveUsers() && ($model->cat_id > 0)) {
            echo Html::a('<img src="/img/redact_icon.png" alt="">' . Yii::t('message', 'frontend.views.client.supp.watch', ['ru'=>'Просмотреть продукты']) , ['client/view-catalog', 'id' => $model->cat_id], [
                'class' => 'redact',
                'style' => 'text-center',
                'data-pjax' => 0,
                'data' => [
                    'target' => '#view-catalog',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                ],
            ]);
        } elseif ($model->cat_id > 0) {
            echo Html::a('<img src="/img/redact_icon.png" alt="">' . Yii::t('message', 'frontend.views.client.supp.edit_two', ['ru'=>'Редактировать продукты']), ['client/edit-catalog', 'id' => $model->cat_id], [
                'class' => 'redact',
                'style' => 'text-center',
                'data-pjax' => 0,
                'data' => [
                    'target' => '#edit-catalog',
                    'toggle' => 'modal',
                    'backdrop' => 'static',]
            ]);
        } else {
//            echo "<span ";
        }
        ?>
        <?=
        Html::a('<img src="/img/corzina_icon.png" alt="">' . Yii::t('message', 'frontend.views.client.supp.del', ['ru'=>'Удалить']), '#', [
            'class' => 'delete-vendor del',
            'data' => ['id' => $model->supp_org_id],
        ]);
        ?>
    </div>
</div><br>