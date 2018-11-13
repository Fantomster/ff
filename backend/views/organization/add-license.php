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
        <?php $allLicenseOrganization = \common\models\licenses\LicenseOrganization::find()->where(['org_id' => $id])->with('license')->asArray()->all(); ?>
        <div class="row">
            <div class="col-md-3">
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
            </div>
            <div class="col-md-9">
                <?php foreach ($allLicenseOrganization as $value): ?>
                    <p <?php if ($value['td'] < $tenDaysAfter) echo 'style ="color: red;"' ?>><?= $value['license']['name'] . " : " . $value['td'] ?></p>
                <?php endforeach; ?>
            </div>
        </div>
        <hr>
        <?php $i++; ?>
    <?php endforeach; ?>
    <hr>
    <h4>Лицензии</h4>
    <hr>
    <?php foreach ($licenses as $id => $name): ?>
        <div class="row">
            <div class="col-md-3">
                <div class="checkbox">
                    <?= Html::checkbox('licenses[]', false, [
                        'value' => $id,
                        'label' => $name,
                        'class' => 'checkbox',
                    ]);
                    ?>
                </div>
            </div>
            <div class="col-md-3">
                <p>Дата окончания</p>
                <div>
                    <?= DatePicker::widget([
                        'name'          => 'td[' . $id . ']',
                        'value'         => date('d.m.Y'),
                        'options'       => [
                            'placeholder' => 'Дата окончания',
                            'class'       => 'delivery-date',
                            'label'       => 'Дата окончания'
                        ],
                        'type'          => DatePicker::TYPE_COMPONENT_APPEND,
                        'layout'        => '{picker}{input}{remove}',
                        'pluginOptions' => [
                            'format'         => 'dd.mm.yyyy',
                            'autoclose'      => true,
                            'startDate'      => "0d",
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
