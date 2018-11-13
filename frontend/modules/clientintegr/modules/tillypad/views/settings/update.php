<?php

use yii\widgets\Breadcrumbs;

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с Tillypad
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/vendorintegr'],
            ],
            'Интеграция с Tillypad',
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
                            'model'              => $model,
                            'dicConst'           => $dicConst,
                            'id'                 => $id,
                            'productSearch'      => $productSearch,
                            'cookingPlaceSearch' => $cookingPlaceSearch,
                            'unitSearch'         => $unitSearch,
                            'sort'               => $sort,
                            'page'               => $page,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    


