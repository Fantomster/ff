<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class="guid_block">
    <div class="guid_block_title">
        <p><?= $model->name ?></p>
    </div>	
    <div class="guid_block_comment">
        <p>Комментарий: <span>нет никаких комментариев</span></p> 
    </div>
    <div class="guid_block_counts">
        <p>Кол-во товаров: <span><?= $model->productCount ?></span></p> 
    </div>
    <div class="guid_block_updated">
        <p>Изменен: <span><?= Yii::$app->formatter->asDatetime($model->updated_at, "php:j M Y H:M:S") ?></span></p> 
    </div>
    <div class="guid_block_buttons">
        <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Удалить</button>
        <?=
            Html::button('<i class="fa fa-trash"></i> Удалить', [
                'class' => 'btn btn-sm btn-outline-danger',
                'data-url' => Url::to(['/order/ajax-delete-guide', 'id' => $model->id]),
            ])
            ?>
        <button class="btn btn-sm btn-outline-default"><i class="fa fa-pencil"></i> Редактировать</button> 
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-to-cart"><i class="fa fa-shopping-cart"></i> В корзину</button>  
    </div>
</div>