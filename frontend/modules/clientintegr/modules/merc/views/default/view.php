<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use api\common\models\merc\mercService;
use api\common\models\merc\MercVsd;

?>
<?php
$lic = mercService::getLicense();
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
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru' => 'Интеграция']),
                'url' => ['/clientintegr/default'],
            ],
            [
                'label' => Yii::t('message', 'frontend.client.integration.mercury', ['ru' => 'Интеграция с системой ВЕТИС "Меркурий"']),
                'url' => ['/clientintegr/merc/default'],
            ],
            'Просмотр ВСД',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <h4><?= Yii::t('message', 'frontend.client.integration.view_vsd', ['ru' => 'Просмотр ВСД']) ?></h4>
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
                    <?= $this->render('_view', ['document' => $document]) ?>
                    <?php
                    if ($document->status == MercVsd::DOC_STATUS_CONFIRMED
                        && (\api\common\models\merc\MercVsd::getType($document->UUID) == 1) && ($lic_merc == 1)) { ?>
                        <div class="col-md-12">
                            <?php
                            echo Html::a(Yii::t('message', 'frontend.client.integration.done', ['ru' => 'Погасить']), ['done', 'uuid' => $document->UUID], ['class' => 'btn btn-success']) . ' ' .
                                Html::a(Yii::t('message', 'frontend.client.integration.done_partial', ['ru' => 'Частичная приёмка']), ['done-partial', 'uuid' => $document->UUID], ['class' => 'btn btn-warning']) . ' ' .
                                Html::a(Yii::t('message', 'frontend.client.integration.return_all', ['ru' => 'Возврат']), ['done-partial', 'uuid' => $document->UUID, 'reject' => true], ['class' => 'btn btn-danger']);
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>
