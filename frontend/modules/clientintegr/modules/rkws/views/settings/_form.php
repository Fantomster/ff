<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use kartik\tree\TreeViewInput;
use yii\bootstrap\Dropdown;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\pdict\DictAgent */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">
    
     <?php $org = User::findOne(Yii::$app->user->id)->organization_id; // var_dump($org); ?>

     <?php $pConst = \api\common\models\RkDicconst::findOne(['id' => $model->const_id]); ?>

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php // echo $form->field($model, 'id')->textInput(['maxlength' => true,'disabled' => 'disabled']) ?>

    <?php // echo $form->field($model, 'const_id')->textInput(['maxlength' => true,'disabled' => 'disabled']) ?>

    <?php switch ($pConst->type) {

        case \api\common\models\RkDicconst::PC_TYPE_DROP : ?>

            <?php if ($pConst->denom === 'taxVat') {

                echo $form->field($model, 'value')->dropDownList([
                    '0' => '0',
                    '1000' => '10',
                    '1800'=>'18'
                ]);
            } else {
                echo $form->field($model, 'value')->dropDownList([
                    '0' => 'Выключено',
                    '1' => 'Включено',
                ]);
            } ?>
    <?php break;
        default: ?>

        <?php echo $form->field($model, 'value')->textInput(['maxlength' => true]) ?>

    <?php } ?>


    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a('Вернуться',
            ['index'],
            ['class' => 'btn btn-success btn-export']);
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

