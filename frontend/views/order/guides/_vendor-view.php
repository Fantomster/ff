<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<td>
    <div class="guid_block_create_title">
        <p><?= $model->vendor->name ?></p>
    </div>	
    <div class="guid_block_create_counts">
        <p>Кол-во товаров: <span><?= $model->vendor->getAvailableProductsCount($model->rest_org_id) ?></span></p> 
    </div>     
</td>
<td>
    <?php if ($model->supp_org_id == $selectedVendor) { 
        //<button class="btn btn-md btn-gray pull-right"><i class="fa fa-thumbs-o-up"></i> Выбран</button>
        echo Html::button('<i class="fa fa-thumbs-o-up"></i> Выбран', [
            'class' => 'btn btn-md btn-gray pull-right disabled selected-vendor',
            'data-url' => Url::to(['/order/ajax-select-vendor', 'id' => $model->supp_org_id]),
        ]);
    } else {
        //<button class="btn btn-md btn-success pull-right"><i class="fa fa-hand-pointer-o"></i> Выбрать</button>  
        echo Html::button('<i class="fa fa-hand-pointer-o"></i> Выбрать', [
            'class' => 'btn btn-md btn-success pull-right select-vendor',
            'data-url' => Url::to(['/order/ajax-select-vendor', 'id' => $model->supp_org_id]),
        ]);
    } ?>
</td>