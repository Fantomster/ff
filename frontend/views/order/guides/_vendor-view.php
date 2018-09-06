<?php
use yii\helpers\Html;
use yii\helpers\Url;

use common\models\RelationSuppRest;
/** @var $model RelationSuppRest */
/** @var $selectedVendor string */

$messVendorProducts = Yii::t('message', 'frontend.views.order.guides.empty_list_four', ['ru'=>'Кол-во товаров:']);
$messVendorChosen = Yii::t('message', 'frontend.views.order.guides.choosed', ['ru'=>'Выбран']);
$messVendorChoose = Yii::t('message', 'frontend.views.order.guides.choose', ['ru'=>'Выбрать']);

?>
<td>
    <div class="guid_block_create_title">
        <p><?= $model->vendor->name ?></p>
    </div>	
    <div class="guid_block_create_counts">
        <p><?= $messVendorProducts ?> <span><?= $model->vendor->getAvailableProductsCount($model->rest_org_id) ?></span></p>
    </div>     
</td>
<td>
    <?php if ($model->supp_org_id == $selectedVendor) { 
        echo Html::button('<i class="fa fa-thumbs-o-up"></i> ' . $messVendorChosen, [
            'class' => 'btn btn-md btn-gray pull-right disabled selected-vendor',
            'data-url' => Url::to(['/order/ajax-select-vendor', 'id' => $model->supp_org_id]),
        ]);
    } else {
        echo Html::button('<i class="fa fa-hand-pointer-o"></i> ' .  $messVendorChoose . ' ', [
            'class' => 'btn btn-md btn-success pull-right select-vendor',
            'data-url' => Url::to(['/order/ajax-select-vendor', 'id' => $model->supp_org_id]),
        ]);
    } ?>
</td>