<?php
use yii\widgets\Breadcrumbs;

$this->title = 'Акт несоответствия';
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
                'url' => ['/clientintegr/default'],
            ],
            [
                'label' => 'Интеграция с системой ВЕТИС "Меркурий"',
                'url' => ['/clientintegr/merc/default'],
            ],
            $this->title,
        ],
    ]) ?>
</section>

<section class="content-header">
    <h4><?= $this->title ?></h4>
</section>

<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="production-act-defect-create">
                        <?php echo $this->render('_form', [
                            'model' => $model,
                            'volume' => $volume
                        ]) ?>

                    </div>
                </div>
            </div>
        </div>
</section>
