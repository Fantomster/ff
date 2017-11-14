<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
$this->title = Yii::t('app', 'Техническая поддержка');
?>
<section class="content-header">
    <h1><?= Yii::t('app', 'Запрос техническому отделу') ?><small> <?= Yii::t('app', 'Ваш запрос будет обработан в порядке очереди и приоритета') ?></small>
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
                        '1'=>Yii::t('app', 'Низкий (пожелание, просьба)'),
                        '2'=>Yii::t('app', 'Ниже среднего (замечание)'),
                        '3'=>Yii::t('app', 'Средний (функционал работает, но не в штатном режиме)'),
                        '4'=>Yii::t('app', 'Выше среднего (Не работает не критичный бизнес процесс)'),
                        '5'=>Yii::t('app', 'Высокий (Система не работает)'),
                        ]);?>
                    <?= $form->field($model, 'body')->textInput() ?> 
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Отправить'), ['id'=>'send','class'=>'btn btn-success']) ?>
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