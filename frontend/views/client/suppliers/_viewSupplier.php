<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\Category;
use yii\helpers\ArrayHelper;
use kartik\checkbox\CheckboxX;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'supplier-form',
            'enableAjaxValidation' => false,
            'action' => Url::toRoute(['client/view-supplier', 'id' => $supplier_org_id]),
            'options' => [
                'class' => 'supplier-form',
            ],
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.client.supp.info', ['ru'=>'Информация об организации']) ?></h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <?=$userStatus==0?
                $form->field($organization, 'name')->textInput(['readonly' => true, 'id'=>'organization-view-supplirs-name']):
                $form->field($organization, 'name')->textInput(['id' => 'organization-view-supplirs-name']);
            ?>
        </div>
        <div class="col-md-6">
            <?=$userStatus==0?
                $form->field($organization, 'city')->textInput(['readonly' => true, 'id'=>'organization-view-supplirs-city']):
                $form->field($organization, 'city')->textInput(['id' => 'organization-view-supplirs-city']);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=$userStatus==0?
                $form->field($organization, 'address')->textInput(['readonly' => true, 'id'=>'organization-view-supplirs-address']):
                $form->field($organization, 'address')->textInput(['id' => 'organization-view-supplirs-address']);
            ?>
        </div>
        <div class="col-md-6">
            <?=$userStatus==0?
                $form->field($organization, 'zip_code')->textInput(['readonly' => true, 'id'=>'organization-view-supplirs-zip_code']):
                $form->field($organization, 'zip_code')->textInput(['id' => 'organization-view-supplirs-zip_code']);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=$userStatus==0?
                $form->field($organization, 'phone')->widget(\common\widgets\PhoneInput::className(), [
                                'jsOptions' => [
                                    'preferredCountries' => ['ru'],
                                    'nationalMode' => false,
                                    'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                ],
                                'options' => [
                                    'class' => 'form-control',
                                    'readonly' => true, 
                                    'id'=>'organization-view-supplirs-phone'
                                ],
                            ]):
                $form->field($organization, 'phone')->widget(\common\widgets\PhoneInput::className(), [
                                'jsOptions' => [
                                    'preferredCountries' => ['ru'],
                                    'nationalMode' => false,
                                    'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                ],
                                'options' => [
                                    'class' => 'form-control',
                                    'id' => 'organization-view-supplirs-phone'
                                ],
                            ]);
            ?>
        </div>
        <div class="col-md-6">
            <?=$userStatus==0?
                $form->field($organization, 'email')->textInput(['readonly' => true, 'id'=>'organization-view-supplirs-email']):
                $form->field($organization, 'email')->textInput(['id' => 'organization-view-supplirs-email']); 
            ?>
            
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=$userStatus==0?
                $form->field($organization, 'website')->textInput(['readonly' => true, 'id'=>'organization-view-supplirs-website']):
                $form->field($organization, 'website')->textInput(['id' => 'organization-view-supplirs-website']);
            ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.client.supp.save_two', ['ru'=>'Сохранить']), ['class' => 'btn btn-success save-form']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close_four', ['ru'=>'Закрыть']) ?></a>
</div>
<?php ActiveForm::end(); ?>