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
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-danger alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        <h4>
            <i class="icon fa fa-check"></i><?= Yii::t('message', 'frontend.views.vendor.error', ['ru' => 'Ошибка']) ?>
        </h4>
        <?= Yii::$app->session->getFlash('success') ?>
    </div>
<?php endif; ?>
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
