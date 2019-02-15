<?php

use yii\widgets\DetailView;
use api\common\models\merc\mercService;
use Yii;

?>
<?php
$lic = mercService::getLicense(Yii::$app->user->identity->organization_id);
$timestamp_now = time();
($lic->status_id == 1) && ($timestamp_now <= (strtotime($lic->td))) ? $lic_merc = 1 : $lic_merc = 0;
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.client.integration.view_vsd', ['ru' => 'Просмотр ВСД']) ?></h4>
</div>
<div class="modal-body">
    <div class="box-header with-border">
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
<div class="modal-footer">
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i
                class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close_four', ['ru' => 'Закрыть']) ?>
    </a>
</div>
