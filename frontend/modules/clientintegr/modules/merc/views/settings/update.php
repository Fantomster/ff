<?php

use yii\widgets\Breadcrumbs;

?>
<section class="content-header">
    <h1>
        <img src="/frontend/web/img/mercuriy_icon.png" style="width: 32px;">
        Интеграция с системой ВЕТИС "Меркурий"
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/vendorintegr'],
            ],
            'Интеграция с iiko Office',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <h4>Редактирование настройки: <?php echo "<strong>" . $dicConst->comment . " </strong>"; ?></h4>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding" style="overflow-x:visible;">
                        <?php echo $this->render('_form', [
                            'model' => $model,
                            'dicConst' => $dicConst
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    


