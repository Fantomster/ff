<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\CountryVat;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\CountryVat */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Создание перечня ставок налогов';
$this->params['breadcrumbs'][] = ['label' => 'Ставки налогов', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if ($count > 0) {
    ?>
    <div class="vats-create">

        <section class="content">
            <h3>Создание перечня ставок налогов</h3>

            <div class="box box-info order-history">
                <div class="box-body">

                    <?php $form = ActiveForm::begin([
                        'enableClientValidation' => false,
                    ]); ?>

                    <?= $form->field($model, 'country')->dropDownList(ArrayHelper::map(CountryVat::getListNotVatCountries(), 'uuid', 'name')); ?>

                    <?= $form->field($model, 'vats')->textInput(['maxlength' => true])->label('Ставки налогов') ?>

                    <div class="form-group">
                        <?= Html::submitButton('Создать', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </section>
    </div>
    <?php
} else {
    ?>
    <div class="vats-create">

        <section class="content">
            <h3>Создание перечня ставок налогов</h3>

            <div class="box box-info order-history">
                <div class="box-body">
                    </br>
                    <h4><?php print 'Создание нового перечня ставок невозможно. Перечни ставок созданы для всех стран.'; ?></h4>
                    </br>
                </div>
                <div class="vats-country-button">
                    <p>
                        <?= Html::a('Вернуться', ['index'], ['class' => 'btn btn-success']) ?>
                    </p>
                    <br>
                </div>

            </div>
    </div>
    </section>
    </div>
    <?php
}
?>