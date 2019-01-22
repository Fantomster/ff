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
    <h4><?= Yii::t('message', 'frontend.client.integration.mercury.edit_settings', ['ru' => 'Редактирование настройки']) ?>
        : <?php echo "<strong>" . $dicConst->comment . " </strong>"; ?></h4>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding" style="overflow-x:visible;">
                        <?php echo $this->render('_form', [
                            'model'    => $model,
                            'dicConst' => $dicConst,
                            'org_list' => $org_list,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    


