<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\search\JournalSearch */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="journal-search">
    <a class="btn btn-primary pull-right" data-toggle="collapse" href="#search_form" aria-expanded="false"
       aria-controls="search_form">
        Форма поиска
    </a>
    <div class="collapse" id="search_form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>

        <?= $form->field($model, 'service_id')->dropDownList(\yii\helpers\ArrayHelper::map(
            \common\models\AllService::find()->all(), 'id', 'denom'
        )) ?>

        <?php
        $organizations = \common\models\Journal::find()
            ->distinct()->select('organization_id')->all();
        $items = [];
        if (!empty($organizations)) {
            foreach ($organizations as $organization) {
                $items[$organization->organization_id] = \common\models\Organization::findOne($organization->organization_id)->name;
            }
        }
        echo $form->field($model, 'organization_id')->dropDownList($items);
        ?>

        <?php echo $form->field($model, 'type')->dropDownList(['success' => 'Успех', 'error' => 'Ошибка']) ?>

        <?php // echo $form->field($model, 'created_at') ?>

        <div class="form-group">
            <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Сброс', '/journal/index', ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
