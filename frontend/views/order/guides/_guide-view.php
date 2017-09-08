<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="guid_block_title">
    <p><?=
        Html::a($model->name, ['/order/edit-guide', 'id' => $model->id], [
            'class' => 'link-edit-guide',
        ])
        ?></p>
</div>	
<div class="guid_block_counts">
    <p>Кол-во товаров: <span><?= $model->productCount ?></span></p> 
</div>
<div class="guid_block_updated">
    <p>Изменен: <span><?= Yii::$app->formatter->asDatetime($model->updated_at, "php:j M Y") ?></span></p> 
</div>
<div class="guid_block_buttons">
    <?=
    Html::button('<i class="fa fa-trash"></i> Удалить', [
        'class' => 'btn btn-sm btn-outline-danger delete-guide',
        'data-url' => Url::to(['/order/ajax-delete-guide', 'id' => $model->id]),
    ])
    ?>
    <?=
    Html::a('<i class="fa fa-pencil"></i> Редактировать', ['/order/edit-guide', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-outline-default',
    ])
    ?>
    <?=
    Html::a('<i class="fa fa-shopping-cart"></i> В корзину', ['order/ajax-show-guide', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-success',
        'data' => [
            'target' => '#guideModal',
            'toggle' => 'modal',
            'backdrop' => 'static',
        ]
    ]);
    ?>
</div>