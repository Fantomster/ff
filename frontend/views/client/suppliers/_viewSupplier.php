<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\models\Category;
use yii\helpers\ArrayHelper;
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
            <?=
                $form->field($organization, 'name')->textInput(['readonly' => true]);
            ?>
        </div>
        <div class="col-md-6">
            <?=
                $form->field($organization, 'city')->textInput(['readonly' => true]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=
                $form->field($organization, 'address')->textInput(['readonly' => true]);
            ?>
        </div>
        <div class="col-md-6">
            <?=
                $form->field($organization, 'zip_code')->textInput(['readonly' => true]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=
                $form->field($organization, 'phone')->textInput(['readonly' => true]);
            ?>
        </div>
        <div class="col-md-6">
            <?=
                $form->field($organization, 'email')->textInput(['readonly' => true]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?=
                $form->field($organization, 'website')->textInput(['readonly' => true]);
            ?>
        </div>
    </div>
    <?php 
    
        
    ?>
    <?php 
    echo '<label class="control-label">Категория</label>';
    echo Select2::widget([
        'name'=>'relationCategory',
        'value' => $load_data,
        'data' => ArrayHelper::map(Category::find()->all(),'id','name'),
        'options' => ['placeholder' => 'Выбрать категорию...', 'multiple' => true],
        'pluginOptions' => [
            'tags' => true,
        ],
    ]);
    ?>
    <?php /*= $form->field($relationCategory, 'category_id')->widget(Select2::classname(), [
        'value' => $load_data,
        'data' => Category::allCategory(),
        'theme' => Select2::THEME_BOOTSTRAP,
        'language' => 'ru',
        'options' => ['multiple' => true,'placeholder' => 'Выбрать категорию...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);*/
    ?>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-primary" data-dismiss="modal">Закрыть</a>
    <?= Html::button('Сохранить', ['class' => 'btn btn-success save-form']) ?>
</div>
<?php ActiveForm::end(); ?>