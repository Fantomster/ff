<?php
use yii\widgets\Breadcrumbs;

$this->title = 'Акт несоответствия';
?>

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

<section class="content">
    <h4><?= $this->title ?></h4>
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
