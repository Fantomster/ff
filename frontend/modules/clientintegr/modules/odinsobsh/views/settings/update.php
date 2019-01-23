<?php

use yii\widgets\Breadcrumbs;

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с 1С Общепит
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/clientintegr'],
            ],
            'Интеграция с 1С Общепит',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    Редактирование настройки: <?php echo "<strong>" . $dicConst->comment . " </strong>"; ?>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding" style="overflow-x:visible;">
                        <?php echo $this->render('_form', [
                            'model'    => $model,
                            'dicConst' => $dicConst
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    


