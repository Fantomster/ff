<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<td>
    <div class="guid_block_create_title">
        <p><?= $model["product"] ?></p>
    </div>	
    <div class="guid_block_create_counts">
        <p>Ед. измерения: <span><?= $model["ed"] ?></span></p> 
    </div>     
</td>
<td>
    <?php
    if (in_array($model["id"], $guideProductList)) {
        //<button class="btn btn-md btn-gray pull-right"><i class="fa fa-thumbs-o-up"></i> Продукт добавлен</button>
        echo Html::button('<i class="fa fa-thumbs-o-up"></i> Продукт добавлен', [
            'class' => 'btn btn-md btn-gray pull-right disabled in-guide',
            'id' => 'product' . $model['id'],
            'data-url' => Url::to(['/order/ajax-add-to-guide', 'id' => $model["id"]]),
        ]);
    } else {
        //<button class="btn btn-md btn-success pull-right"><i class="fa fa-plus"></i> Добавить в шаблон</button> 
        echo Html::button('<i class="fa fa-plus"></i> Добавить в шаблон', [
            'class' => 'btn btn-md btn-success pull-right add-to-guide',
            'id' => 'product' . $model['id'],
            'data-url' => Url::to(['/order/ajax-add-to-guide', 'id' => $model["id"]]),
        ]);
    }
    ?>
</td>
