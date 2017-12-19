<?php

use yii\helpers\Html;
?>
<div class="franchisee-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <?=
    \yii\widgets\Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Список заявок',
                'url' => ['request/index'],
            ],
            'Редактирование отклика на заявку',
        ],
    ])
    ?>

    <?= $this->render('_form-callback', [
        'model' => $model,
        'suppliersArray' => $suppliersArray
    ]) ?>

</div>
