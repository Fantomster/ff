<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="guid_block_title">
    <p><?= $model->name ?></p>
</div>	
<div class="guid_block_counts">
    <p>Кол-во товаров: <span><?= $model->productCount ?></span></p> 
</div>
<div class="guid_block_updated">
    <p>Изменен: <span><?= Yii::$app->formatter->asDatetime($model->updated_at, "php:j M Y H:M:S") ?></span></p> 
</div>
<div class="guid_block_buttons">
    <?=
    Html::button('<i class="fa fa-trash"></i> Удалить', [
        'class' => 'btn btn-sm btn-outline-danger',
        'data-url' => Url::to(['/order/ajax-delete-guide', 'id' => $model->id]),
    ])
    ?>
    <?=
    Html::a('<i class="fa fa-pencil"></i> Редактировать', ['/order/edit-guide', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-outline-default',
    ])
    ?>
    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-to-cart"><i class="fa fa-shopping-cart"></i> В корзину</button>  
</div>