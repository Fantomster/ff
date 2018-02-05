<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use kartik\tree\TreeViewInput;
use yii\bootstrap\Dropdown;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\pdict\DictAgent */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">
    <?php
    $orga = $model->organization;
    $data = ($orga != null)?([$orga->id=>$orga->name]):([]);
    // $smodel = $model->service;
    ?>
    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>
    
    <?php // echo $form->field($model, 'code')->textInput(['maxlength' => true,'disabled'=>'disabled']) ?>
    <?php // echo $form->field($model, 'name')->textInput(['maxlength' => true,'disabled'=>'disabled']) ?>

    <?php echo $form->field($model, 'org')->widget(Select2::classname(), [
            'data' => $data,
            'options' => ['placeholder' => 'Выберите организацию по ID или Наименованию',
                ],
             'pluginOptions' => [
                    'minimumInputLength' => 2,
                    'ajax' => [
                        'url' => Url::toRoute('autocomplete'),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {term:params.term}; }')
                    ],
                    'allowClear' => true
                ],
                'pluginEvents' => [
                    //"select2:select" => "function() { alert(1);}",
                    "select2:select" => "function() {
                        if($(this).val() == 0)
                        {
                            $('#agent-modal').modal('show');
                        }
                    }",
                    ]
            ]); ?>
    
      <?php 
  
          if (!$model->fd) {
            $model->fd = date('d.m.Y',time());        
            } else {
            $rdate = date('d.m.Y',strtotime($model->fd));
          //  var_dump($rdate);
            // $rdate->format('m/d/y h:i a');    
            $model->fd = $rdate;
            }
  ?>
       <?php   echo $form->field($model, 'fd')->label('Активно с')->
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
      <?php 
  
          if (!$model->td) {
            $model->td = date('d.m.Y',time());        
            } else {
            $rdate = date('d.m.Y',strtotime($model->td));
          //  var_dump($rdate);
            // $rdate->format('m/d/y h:i a');    
            $model->td = $rdate;
            }
  ?>
       <?php   echo $form->field($model, 'td')->label('Актуально по')->
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
    
      <?php  echo $form->field($model, 'status_id')->dropDownList([1 => 'Неактивно', 2 => 'Активно']); ?>
    
    <?php // echo $form->field($model, 'num_code')->hiddenInput(['value' => Yii::$app->user->identity->userProfile->branch_id])->label(''); ?>


    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a('Вернуться',
            ['rkws/index'],
            ['class' => 'btn btn-success btn-export']);
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

