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
    <h3><?= Yii::t('app', 'Редактирование заявки №') ?> <?= $model->id ?></h3>
    <div class="box box-info order-history">
        <div class="box-body">

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'category')->dropDownList(common\models\MpCategory::allCategory()) ?>

            <?= $form->field($model, 'product')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

            <?= $form->field($model, 'regular')->dropDownList([1=>Yii::t('app', 'Разово'),2=>Yii::t('app', 'Ежедневно'),3=>Yii::t('app', 'Каждую неделю'),4=>Yii::t('app', 'Каждый месяц')]); ?>

            <?= $form->field($model, 'rush_order')->checkbox();?>

            <?= $form->field($model, 'payment_method')->dropDownList([1=>Yii::t('app', 'Наличный расчет'),2=>Yii::t('app', 'Безналичный расчет')]); ?>

            <?= $form->field($model, 'deferment_payment')->textInput()->label(Yii::t('app', 'Отложенный платеж(дней)')) ?>

            <hr>
            <?=$form->field($model, 'active_status')->widget(CheckboxX::classname(), [
                'autoLabel' => true,
                'model' => $model,
                'attribute' => 'rush_order',
                'pluginOptions'=>[
                    'threeState'=>false,
                    'theme' => 'krajee-flatblue',
                    'enclosedLabel' => false,
                    'size'=>'lg',
                ],
                'labelSettings' => [
                    'label' => ' ' . Yii::t('app', 'Заявка открыта') . '  <span style="font-size:14px;color:#ccc;margin-left:5px"> ' . Yii::t('app', 'уберите галочку, чтобы закрыть заявку') . ' </span>',
                    'position' => CheckboxX::LABEL_RIGHT,
                    'options' =>['style'=>'font-size: 20px;color: red;font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;font-weight: 500;']
                ]
            ])->label(false);?>
            <hr>

            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</section>
