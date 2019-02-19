<?php

use yii\helpers\Html;
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
            <?= $this->render('_view', ['document' => $document]) ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?php if ($document->status == MercVsd::DOC_STATUS_CONFIRMED
        && (\api\common\models\merc\MercVsd::getType($document->UUID) == 1) && ($lic_merc == 1)) {
        echo Html::a(Yii::t('message', 'frontend.client.integration.done', ['ru' => 'Погасить']), ['done', 'uuid' => $document->UUID], ['class' => 'btn btn-success']) . ' ' .
            Html::a(Yii::t('message', 'frontend.client.integration.done_partial', ['ru' => 'Частичная приемка']), ['done-partial', 'uuid' => $document->UUID], ['class' => 'btn btn-warning', 'data' => [
                //'pjax'=>0,
                'target'   => '#ajax-load',
                'toggle'   => 'modal',
                'backdrop' => 'static',
            ],]) . ' ' .
            Html::a(Yii::t('message', 'frontend.client.integration.return_all', ['ru' => 'Возврат']), ['done-partial', 'uuid' => $document->UUID, 'reject' => true], ['class' => 'btn btn-danger', 'data' => [
                //'pjax'=>0,
                'target'   => '#ajax-load',
                'toggle'   => 'modal',
                'backdrop' => 'static',
            ],]);
    } ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i
                class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close_four', ['ru' => 'Закрыть']) ?>
    </a>
</div>
