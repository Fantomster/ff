<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use kartik\tree\TreeViewInput;
use yii\bootstrap\Dropdown;

/* @var $this yii\web\View */
/* @var $model common\models\pdict\DictAgent */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">
    
    <?php  if(empty($model->store_rid)) $model->store_rid = 1; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'order_id')->textInput(['maxlength' => true,'disabled' => 'disabled']) ?>
    
    <?php echo $form->field($model, 'text_code')->textInput(['maxlength' => true]) ?>
    
    <?php echo $form->field($model, 'num_code')->textInput(['maxlength' => true]) ?>

  <?php  echo $form->field($model, 'corr_rid')->dropDownList(ArrayHelper::map(api\common\models\RkAgent::find()->all(), 'rid', 'denom')) ?>
    
  <?php  // echo $form->field($model, 'store_rid')->dropDownList(ArrayHelper::map(api\common\models\RkStore::find()->all(), 'rid', 'denom')) ?>  
    
   <div class="box-body table-responsive no-padding" style="overflow-x:visible; overflow-y:visible; height: 100%;"> 
    <?php 
             echo $form->field($model, 'store_rid')->widget(TreeViewInput::classname(),
                                                    [
                                                        'name' => 'store_rid',
                                                        'value' => 'true', // preselected values
                                                        'query' => api\common\models\RkStoretree::find()->addOrderBy('root, lft'),
                                                      //  'headingOptions' => ['label' => 'Склады'],
                                                        'rootOptions' => ['label'=>'Корень'],
                                                        'fontAwesome' => true,
                                                        'asDropdown' => true,
                                                        'multiple' => false,
                                                        'options' => ['disabled' => false]
                                                    ]);
    
    ?>
   </div>
  <?php 
  
          if (!$model->doc_date) {
            $model->doc_date = date('d.m.Y',time());        
            } else {
            $rdate = date('d.m.Y',strtotime($model->doc_date));
          //  var_dump($rdate);
            // $rdate->format('m/d/y h:i a');    
            $model->doc_date = $rdate;
            }
  ?>
       <?php   echo $form->field($model, 'doc_date')->label('Дата Документа')->
        widget(DatePicker::classname(), [
                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                'convertFormat' => true,
                'layout' => '{picker}{input}',
             //   'disabled'=>$disable,
                'pluginOptions' => [
                    'autoclose'=>true,
                 //   'format' => 'Y-m-d',
                     'format' => 'dd.MM.yyyy',
                 //     'format' => 'yyyy.MM.dd',
                //    'startDate' => $model->startDate,
                //    'endDate' => $model->endDate,
                    'todayHighlight' => false,
                   
                    
                   
                    ],
        ]);  

?>

    <?php echo $form->field($model, 'note')->textInput(['maxlength' => true]) ?>

    
    <?php // echo $form->field($model, 'num_code')->hiddenInput(['value' => Yii::$app->user->identity->userProfile->branch_id])->label(''); ?>


    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a('Вернуться',
            ['index'],
            ['class' => 'btn btn-success btn-export']);
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

