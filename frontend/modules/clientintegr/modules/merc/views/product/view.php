<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\DetailView;
use Yii;
use api\common\models\merc\mercService;
use api\common\models\merc\MercVsd;

?>
<?php
$lic = mercService::getLicense(Yii::$app->user->identity->organization_id);
$timestamp_now = time();
($lic->status_id == 1) && ($timestamp_now <= (strtotime($lic->td))) ? $lic_merc = 1 : $lic_merc = 0;
?>
<section class="content-header">
    <h1>
        <img src="<?= Yii::$app->request->baseUrl ?>/img/mercuriy_icon.png" style="width: 32px;">
        <?= Yii::t('message', 'frontend.client.integration.mercury', ['ru' => 'Интеграция с системой ВЕТИС "Меркурий"']) ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru' => 'Интеграция']),
                'url'   => ['/clientintegr/default'],
            ],
            [
                'label' => 'Справочники продукции',
                'url'   => ['/clientintegr/merc/product'],
            ],
            'Просмотр ВСД',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <h4>Просмотр сведений о продукте</h4>
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
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
    </div>
</section>
