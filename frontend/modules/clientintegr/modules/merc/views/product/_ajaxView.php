<?php

use yii\widgets\DetailView;
use api\common\models\merc\mercService;
use api\common\models\merc\MercVsd;

?>
<?php
$lic = mercService::getLicense(Yii::$app->user->identity->organization_id);
$timestamp_now = time();
($lic->status_id == 1) && ($timestamp_now <= (strtotime($lic->td))) ? $lic_merc = 1 : $lic_merc = 0;
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Просмотр сведений о продукте</h4>
</div>
<div class="modal-body">
    <div class="box-header with-border">
        <div class="box-body table-responsive no-padding grid-category">
            <?php echo DetailView::widget([
                'model'      => $model,
                'attributes' => [
                    [
                        'attribute' => 'productType',
                        'format'    => 'raw',
                        'value'     => MercVsd::$product_types[$model->productType],
                    ],
                    [
                        'attribute' => 'product_uuid',
                        'format'    => 'raw',
                        'value'     => $model->product->name,
                    ],
                    [
                        'attribute' => 'subproduct_uuid',
                        'format'    => 'raw',
                        'value'     => $model->subProduct->name,
                    ],
                    [
                        'attribute' => 'name',
                        'format'    => 'raw',
                        'value'     => $model->name,
                    ],
                    [
                        'attribute' => 'code',
                        'format'    => 'raw',
                        'value'     => $model->code,
                    ],
                    [
                        'attribute' => 'globalID',
                        'format'    => 'raw',
                        'value'     => $model->globalID ?? '-',
                    ],
                    [
                        'attribute' => 'correspondsToGost',
                        'format'    => 'raw',
                        'value'     => $model->correspondsToGost ? ($model->gost ?? null) : 'Нет',
                    ],
                    /*[
                        'attribute' => 'packagingType_uuid',
                        'format' => 'raw',
                        'value' => $model->packingType->name ?? null,
                    ],
                    [
                        'attribute' => 'unit_uuid',
                        'format' => 'raw',
                        'value' => $model->unit->name ?? null,
                    ],*/
                ],
            ]) ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i
                class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close_four', ['ru' => 'Закрыть']) ?>
    </a>
</div>
