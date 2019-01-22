<?php

use yii\widgets\Breadcrumbs;

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
                'url'   => ['/clientintegr'],
            ],
            Yii::t('message', 'frontend.client.integration.mercury', ['ru' => 'Интеграция с системой ВЕТИС "Меркурий"']),
        ],
    ])
    ?>
</section>
<section class="content-header">
    <h4><?= Yii::t('message', 'frontend.client.integration.mercury.add_stock_entry', ['ru' => 'Добавление входной продукции на предприятие']) ?></h4>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding" style="overflow-x:visible;">
                        <h4>Сведения об отправителе: </h4>
                        <?php $owner = \frontend\modules\clientintegr\modules\merc\models\createStoreEntryForm::getOwner(); ?>
                        <table id="w1" class="table table-striped table-bordered detail-view">
                            <tbody>
                            <tr>
                                <th>Название предприятия</th>
                                <td><?= $owner[0] ?></td>
                            </tr>
                            <tr>
                                <th>Хозяйствующий субъект (владелец продукции):</th>
                                <td><?= $owner[1] ?></td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="dict-agent-form">
                            <?php echo $this->render('_mainForm', [
                                'model'          => $model,
                                'productionDate' => $productionDate,
                                'expiryDate'     => $expiryDate,
                                'inputDate'      => $inputDate,
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>