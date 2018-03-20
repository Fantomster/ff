<?php

$this->title = 'Настройка подключения ' . $model->id;
?>

<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> <?= $this->title ?>
    </h1>
    <?=
    \yii\widgets\Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr/default'],
            ],
            [
                'label' => 'Интеграция Email: ТОРГ - 12',
                'url' => ['/clientintegr/email/default'],
            ],
            $this->title,
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <?= \Yii::$app->controller->module->renderMenu() ?>
            </div>
            <div class="box-body">
                <?= $this->render('_form', ['model' => $model, 'user' => $user]) ?>
            </div>
        </div>
    </div>
</section>