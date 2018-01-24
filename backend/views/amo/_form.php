<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
kartik\select2\Select2Asset::register($this);
use kartik\checkbox\CheckboxX;
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);

/* @var $this yii\web\View */
/* @var $model common\models\Franchisee */
/* @var $form yii\widgets\ActiveForm */
?>

<section class="content">
    <h3>Редактирование поля <?= $model->amo_field ?></h3>
    <div class="box box-info order-history">
        <div class="box-body">

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'amo_field')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'responsible_user_id')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'pipeline_id')->textInput(['maxlength' => true]) ?>

            <hr>

            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</section>
