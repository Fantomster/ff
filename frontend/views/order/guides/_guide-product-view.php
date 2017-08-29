<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<td>
    <div class="guid_block_create_title">
        <p><?= $model->product ?></p>
    </div>	
    <div class="guid_block_create_counts">
        <p><?= $model->vendor->name ?></p> 
    </div>     
</td>
<td>
    <a class="btn btn-md btn-outline-danger pull-right"><i class="fa fa-trash"></i></a>     
    <?=
    Html::button('<i class="fa fa-trash"></i>', [
        'class' => 'btn btn-md btn-outline-danger pull-right',
        'data-url' => Url::to(['/order/edit-guide', 'id' => $model->id]),
    ])
    ?>
</td>
