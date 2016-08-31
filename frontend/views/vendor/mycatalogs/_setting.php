<?php
use yii\helpers\Html;
use yii\helpers\url;
use yii\web\View;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\RelationSuppRest;
use common\models\User;
use common\models\Organization;
?>
<?php
$relationSuppRest = new RelationSuppRest;

$currentUser = User::findIdentity(Yii::$app->user->id);
//$rest_org_name = Organization::findIdentity(Yii::$app->user->id);

$data=ArrayHelper::map(RelationSuppRest::find()
->where(['sup_org_id'=>$currentUser->organization_id,'status'=>0,'invite'=>1])
->all(),'id','rest_org_id');

$form = ActiveForm::begin([
            'id' => 'update-form',
            'enableAjaxValidation' => false,
            'action' => Url::toRoute('vendor/_success'),
        ]);
        
?>
<div class="modal-header">	
</div>
<div class="modal-body">
<?php  

?>
<?= $form->field($relationSuppRest, 'rest_org_id')->widget(Select2::classname(), [
		'data' => $data,
		'theme' => Select2::THEME_BOOTSTRAP,
		'language' => 'ru',
		'options' => ['multiple' => true,'placeholder' => 'Назначить каталог...'],
		'pluginOptions' => [
		'allowClear' => true
				    ],
				]);
?>
</div>
<div class="modal-footer">
	<a href="#" class="btn btn-default" data-dismiss="modal">Закрыть</a>
    <?= Html::button('Сохранить', ['class' => 'btn btn-success']) ?>
</div>
<?php ActiveForm::end(); ?>