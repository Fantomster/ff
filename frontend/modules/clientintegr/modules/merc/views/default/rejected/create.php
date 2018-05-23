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
<div class="production-act-defect-create">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
