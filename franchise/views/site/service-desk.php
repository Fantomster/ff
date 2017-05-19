<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
$this->title = 'ServiceDesk';
?>
<section class="content-header">
    <h1>Запрос техническому отделу<small> Ваш запрос будет обработан в порядке очереди!</small>
    </h1>
</section>
<section class="content">
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
        <div class="box box-info">
            <div id="collapseOne" class="panel-collapse collapse in" aria-expanded="true">
                <div class="box-body">
                    <?= $form->field($model, 'priority')->dropDownList([
                        '1'=>'Низкий',
                        '2'=>'Ниже среднего',
                        '3'=>'Средний',
                        '4'=>'Выше среднего',
                        '5'=>'Высокий',
                        ]);?>
                    <?= $form->field($model, 'body')->textInput() ?> 
                    <div class="form-group">
                        <?= Html::submitButton('Отправить', ['id'=>'send','class'=>'btn btn-success']) ?>
                    </div>
                </div>
            </div>
        </div>
      
      <?php 
      ActiveForm::end(); 
      Pjax::end();
      ?>  
    </div>
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