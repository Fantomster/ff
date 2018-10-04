<?php

use yii\helpers\Html;
use \kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Создание лицензий';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="organization-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php $form = \yii\widgets\ActiveForm::begin(); ?>
    <h3>Выберите организации</h3>
    <?php $i = 0; ?>
    <?php foreach ($organizations as $id => $name): ?>
        <div class="checkbox">
            <?php if ($i == 0): ?>
        <p style="font-weight: bold">
        <?php else: ?>
            <p style="padding-left: 15px;">
                <?php endif; ?>
                <?= Html::checkbox('organizations[]', true, [
                    'value' => $id,
                    'label' => $name,
                    'class' => 'checkbox',
                ]);
                ?>
                <?php if ($i == 0): ?>
            </p>
        <?php else: ?>
            </p>
        <?php endif; ?>
        </div>
        <?php $i++; ?>
    <?php endforeach; ?>
    <hr>
    <h4>Лицензии</h4>
    <hr>
    <?php foreach ($services as $id => $denom): ?>
        <div class="row">
            <div class="col-md-3">
                <div class="checkbox">
                    <?= Html::checkbox('services[]', false, [
                        'value' => $id,
                        'label' => $denom,
                        'class' => 'checkbox',
                    ]);
                    ?>
                </div>
            </div>
            <div class="col-md-3">
                <p>Дата окончания</p>
                <div>
                    <?= DatePicker::widget([
                        'name' => 'td[' . $id . ']',
                        'value' => date('d.m.Y'),
                        'options' => [
                            'placeholder' => 'Дата окончания',
                            'class' => 'delivery-date',
                            'label' => 'Дата окончания'
                        ],
                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                        'layout' => '{picker}{input}{remove}',
                        'pluginOptions' => [
                            'format' => 'dd.mm.yyyy',
                            'autoclose' => true,
                            'startDate' => "0d",
                            'todayHighlight' => true,
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
        <hr>
    <?php endforeach; ?>

    <?= Html::submitButton('Создать', ['class' => 'btn btn-success']) ?>
    <?php \yii\widgets\ActiveForm::end(); ?>
</div>
