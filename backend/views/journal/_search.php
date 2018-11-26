<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\search\JournalSearch */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="journal-search row">
    <div id="search_form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ]
        ]); ?>

        <div class="col-sm-3">
            <?php
            echo $form->field($model, 'service_id')->widget(\kartik\select2\Select2::classname(), [
                'data' => \yii\helpers\ArrayHelper::map(\common\models\AllService::find()->all(), 'id', 'denom'),
                'attribute' => 'service_id',
                'pluginOptions' => [
                    'selected' => \Yii::$app->request->get('service_id') ?? '',
                    'allowClear' => false,
                ],
            ]);
            ?>
        </div>
        <div class="col-sm-3">
            <?php
            $organizations = \common\models\Journal::find()
                ->distinct()->select('organization_id')->all();
            $items = [null => 'Все'];
            /*if (!empty($organizations)) {
                foreach ($organizations as $organization) {
                    $items[$organization->organization_id] = isset($organization->organization_id) ? \common\models\Organization::findOne($organization->organization_id)->name : '';
                }
            }*/

            echo $form->field($model, 'organization_id')->widget(\kartik\select2\Select2::classname(), [
                'data' => $items,
                'attribute' => 'organization_id',
                'pluginOptions' => [
                    'selected' => \Yii::$app->request->get('organization_id') ?? '',
                    'allowClear' => false,
                ],
            ]);
            ?>
        </div>
        <div class="col-sm-3">
            <?php
            $service_id = $model->service_id ?? 1;
            echo $form->field($model, 'operation_code')->widget(\kartik\select2\Select2::classname(), [
                'data' => \yii\helpers\ArrayHelper::map(\common\models\AllServiceOperation::find()->select('code, ifnull(`comment`, `denom`) as denom')
                    ->where(['service_id' => $service_id])
                    ->all(), 'code', 'denom'),
                'attribute' => 'operation_code',
                'pluginOptions' => [
                    'selected' => \Yii::$app->request->get('code') ?? '',
                    'allowClear' => false,
                ],
            ]);?>
        </div>
        <div class="col-sm-2">
            <?php echo $form->field($model, 'type')->dropDownList([null => 'Все', 'success' => 'Успех', 'error' => 'Ошибка']) ?>
        </div>


        <div class="col-sm-1">
            <?= Html::label('&nbsp;'); ?><br>
            <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
