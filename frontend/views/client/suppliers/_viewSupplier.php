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
            <?=empty($user)?
                $form->field($organization, 'name')->textInput(['readonly' => true]):
                $form->field($organization, 'name');//->textInput(['readonly' => true]);
            ?>
        </div>
        <div class="col-md-6">
            <?=empty($user)?
                $form->field($organization, 'city')->textInput(['readonly' => true]):
                $form->field($organization, 'city');//
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=empty($user)?
                $form->field($organization, 'address')->textInput(['readonly' => true]):
                $form->field($organization, 'address');
            ?>
        </div>
        <div class="col-md-6">
            <?=empty($user)?
                $form->field($organization, 'zip_code')->textInput(['readonly' => true]):
                $form->field($organization, 'zip_code');
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=empty($user)?
                $form->field($organization, 'phone')->textInput(['readonly' => true]):
                $form->field($organization, 'phone');
            ?>
        </div>
        <div class="col-md-6">
            <?=empty($user)?
                $form->field($organization, 'email')->textInput(['readonly' => true]):
                $form->field($organization, 'email'); 
            ?>
            
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=empty($user)?
                $form->field($organization, 'website')->textInput(['readonly' => true]):
                $form->field($organization, 'website');
            ?>
        </div>
        <div class="col-md-6">
            <?=empty($user)?'':
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