<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\DetailView;
use api\common\models\merc\mercService;
use Yii;
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
                'label' => Yii::t('message', 'frontend.client.integration.mercury', ['ru' => 'Интеграция с системой ВЕТИС "Меркурий"']),
                'url'   => ['/clientintegr/merc/default'],
            ],
            'Просмотр ВСД',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <h4><?= Yii::t('message', 'frontend.client.integration.view_stock_entry', ['ru' => 'Просмотр сведений о записи входного журнала ']) ?></h4>
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-check"></i><?= Yii::t('message', 'frontend.client.integration.mercury.successful', ['ru' => 'Выполнено']) ?>
                            </h4>
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-exclamation-circle"></i><?= Yii::t('message', 'frontend.views.vendor.error', ['ru' => 'Ошибка']) ?>
                            </h4>
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>
                    <h4>Общие сведения: </h4>
                    <?php echo DetailView::widget([
                        'model'      => $document,
                        'attributes' => [
                            [
                                'attribute' => 'status',
                                'format'    => 'raw',
                                'value'     => $document->status,
                            ],
                            [
                                'attribute' => 'owner',
                                'format'    => 'raw',
                                'value'     => $document->owner,
                            ],
                            [
                                'attribute' => 'owner_firm',
                                'format'    => 'raw',
                                'value'     => $document->owner_firm,
                            ],
                        ],
                    ]) ?>
                    <h4>Информация о продукции: </h4>
                    <?php echo DetailView::widget([
                        'model'      => $document,
                        'attributes' => [
                            [
                                'attribute' => 'entryNumber',
                                'value'     => $document->entryNumber
                            ],
                            [
                                'attribute' => 'createDate',
                                'value'     => $document->createDate
                            ],
                            [
                                'attribute' => 'productType',
                                'value'     => $document->productType
                            ],
                            [
                                'attribute' => 'product',
                                'value'     => $document->product
                            ],
                            [
                                'attribute' => 'subProduct',
                                'value'     => $document->subProduct
                            ],
                            [
                                'attribute' => 'productName',
                                'value'     => $document->productName
                            ],
                            [
                                'attribute' => 'volume',
                                'value'     => $document->volume . " " . $document->unit
                            ],
                            [
                                'attribute' => 'dateOfProduction',
                                'value'     => $document->dateOfProduction
                            ],
                            [
                                'attribute' => 'expiryDate',
                                'value'     => $document->expiryDate
                            ],
                        ],
                    ]) ?>
                    <h4>Сведения о происхождении продукции: </h4>
                    <?php echo DetailView::widget([
                        'model'      => $document,
                        'attributes' => [
                            [
                                'attribute' => 'producer_country',
                                'value'     => $document->producer_country
                            ],
                            [
                                'attribute' => 'producer',
                                'value'     => $document->producer
                            ],
                        ],
                    ]) ?>
                    <h4>Дополнительная информация о входной продукции: </h4>
                    <?php echo DetailView::widget([
                        'model'      => $document,
                        'attributes' => [
                            [
                                'attribute' => 'uuid_vsd',
                                'value'     => $document->uuid_vsd
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
