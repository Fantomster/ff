<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\models\Category;
use yii\helpers\ArrayHelper;
use kartik\checkbox\CheckboxX;
kartik\select2\Select2Asset::register($this);
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
    <h4 class="modal-title">Информация об организации</h4>
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
                $form->field($organization, 'phone')->textInput(['readonly' => true, 'id'=>'organization-view-supplirs-phone']):
                $form->field($organization, 'phone')->textInput(['id' => 'organization-view-supplirs-phone']);
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
        <div class="col-md-6">
            <?=$userStatus==0?'':
                CheckboxX::widget([
                    'name'=>'resend_email',
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>0,
                    'autoLabel' => true,
                    'options'=>['id'=>'resend_email'],
                    'pluginOptions'=>[
                        'threeState'=>false,
                        'theme' => 'krajee-flatblue',
                        'enclosedLabel' => true,
                        'size'=>'md',
                        ]
                ]) . 
                '<label class="control-label" for="resend_email">Отправить приглашение</label>';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
    <?php 
    echo '<label class="control-label">Категория</label>';
    echo Select2::widget([
        'name'=>'relationCategory',
        'value' => $load_data,
        'theme' => Select2::THEME_BOOTSTRAP,
        'language' => 'ru',
        'data' => ArrayHelper::map(Category::find()->all(),'id','name'),
        'options' => ['placeholder' => 'Выбрать категорию...', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => false
        ],
    ]);
    ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success save-form']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> Закрыть</a>
</div>
<?php ActiveForm::end(); ?>