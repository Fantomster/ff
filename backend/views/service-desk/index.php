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
        <?= Html::submitButton('Отправить', ['class'=>'btn btn-success']) ?>
    </div>
      <?php ActiveForm::end(); ?>  
    </div>
      <a target="_blank" href="https://docs.google.com/spreadsheets/d/19vqYJCAQBGPNLuyJpd4jL6O7MT4CxHUhzC2tCvfUtPQ/edit?usp=sharing">Постоянная ссылка docs.google.com</a>
  </div>
</section>