<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CountryVat */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Редактирование перечня ставок налогов';
$this->params['breadcrumbs'][] = ['label' => 'Ставки налогов', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vats-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <section class="content">
        <h3>Редактирование перечня ставок налогов для страны: <?php echo $model->country->name ?></h3>
        <div class="box box-info order-history">
            <div class="box-body">

                <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'vats')->textInput(['maxlength' => true])->label('Ставки налогов') ?>

                <div class="form-group">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </section>
</div>
