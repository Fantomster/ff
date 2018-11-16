<?php

use yii\helpers\Html;
use \kartik\widgets\DatePicker;
use yii\web\View;

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
                    <div class="row">
                        <div class="col-md-4">
                            <span <?php if ($value['td'] < $tenDaysAfter) echo 'style ="color: red;"' ?>>
                                <?= $value['license']['name'] . " : " . $value['td'] ?>
                            </span>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= Html::label('Стоимость лицензии', 'price') ?>
                                <?= Html::input('number', 'price', $value['price'], ['style' => "width: 200px", 'class' => 'form-control form-control-sm', 'id' => 'alPrice_' . $value['id']]) ?>
                                <div id="alResult_<?= $value['id'] ?>"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <?= Html::label('Удалена?', 'is_deleted') ?>
                            <?= Html::checkbox('is_deleted', $value['is_deleted'], ['id' => 'alIsDeleted_' . $value['id']]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= Html::button('Сохранить', ['license_organization_id' => $value['id'], 'class' => 'btn btn-sm alUpdateLicense']) ?>
                        </div>
                    </div>
                    <hr>
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
                        'class' => 'checkbox alCheckbox',
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
                            'label'       => 'Дата окончания',
                            'disabled'    => 'disabled',
                            'autocomplete' => 'off'
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

<?php
$url = \yii\helpers\Url::to('/organization/ajax-update-license-organization');
$customJs = <<< JS
$(".alUpdateLicense").on("click", function () {
    var licenseOrgId = $(this).attr('license_organization_id');
    var priceInputValue = $("#alPrice_" + licenseOrgId).val();
    var isDeletedValue = $("#alIsDeleted_" + licenseOrgId).prop('checked');
    $.ajax({
     type: 'POST',
     url: "$url",
     data: {
         licenseOrgId: licenseOrgId,
         priceInputValue: priceInputValue,
         isDeletedValue: isDeletedValue
        },
     success: function(result) {
       if(result == 'success') {
           $("#alResult_" + licenseOrgId).html('<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><h4><i class="icon fa fa-check"></i>Сохранено!</h4></div>');
       } else {
           $("#alResult_" + licenseOrgId).html('<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><h4><i class="icon fa fa-check"></i>Ошибка!</h4></div>');
       }
     }
   });
});

$(".alCheckbox").on("click", function () {
    var checked = $(this).prop('checked');
    var value = $(this).val();
    if (checked) {
        $('input[name="td[' + value + ']"]').removeAttr('disabled');
    } else {
        $('input[name="td[' + value + ']"]').attr('disabled', 'disabled');
    }
}); 
JS;
$this->registerJs($customJs, View::POS_READY);
?>
