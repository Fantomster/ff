<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
$this->title = 'ServiceDesk';
?>
<section class="content-header">
    <h1>ServiceDesk <small>Заявки от клиентов системы f-keeper</small>
    </h1>
</section>
<section class="content-body">
  <div class="row">
    <div class="col-md-12">
      <?php
        Pjax::begin(['id'=>'pjax-container-form']);
        $form = ActiveForm::begin([
            'options' => [
                'data-pjax' => true,
                'id' => 'form'
            ],
        ]);
      ?>
        
      <div class="row">
        <div class="col-md-4">
        <?= $form->field($model, 'region')->textInput() ?>   
        </div>
        <div class="col-md-4">
        <?= $form->field($model, 'fio')->textInput() ?>   
        </div>  
        <div class="col-md-4">
        <?= $form->field($model, 'phone')->textInput() ?>   
        </div> 
      </div>
      <div class="row">
        <div class="col-md-12">
        <?= $form->field($model, 'body')->textInput() ?>   
        </div> 
      </div>
      <div class="form-group">
        <?= Html::submitButton('Отправить', ['id'=>'send','class'=>'btn btn-success']) ?>
    </div>
      <?php 
      ActiveForm::end(); 
      Pjax::end();
      ?>  
    </div>
      <a target="_blank" href="https://docs.google.com/spreadsheets/d/19vqYJCAQBGPNLuyJpd4jL6O7MT4CxHUhzC2tCvfUtPQ/edit?usp=sharing">Постоянная ссылка docs.google.com</a>
      <iframe width="100%" height="500px" scrolling="no" id="test"  src="https://docs.google.com/spreadsheets/d/19vqYJCAQBGPNLuyJpd4jL6O7MT4CxHUhzC2tCvfUtPQ/edit?usp=sharing" >    
  </iframe>
  </div>
</section>
<?php
$this->registerJs(
   '$(document).ready(
    $("#form").on("beforeSubmit", function(event, jqXHR, settings) {
        var form = $(this);
        if(form.find(".has-error").length) {
            return false;
        }
        $("#send").prop("disabled", true);
        $.ajax({
            url: form.attr("action"),
            type: "post",
            data: form.serialize(),
            success: function(data) {
                $("#send").prop("disabled", false);
            }
        });
        return false;
    }),
);'
);
?>