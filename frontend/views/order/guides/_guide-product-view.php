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
    <?=
    Html::button('<i class="fa fa-trash"></i>', [
        'class' => 'btn btn-md btn-outline-danger pull-right remove-from-guide',
        'data-url' => Url::to(['/order/ajax-remove-from-guide', 'id' => $model->id]),
        'data-target-id' => 'product' . $model->id,
    ])
    ?>
</td>
