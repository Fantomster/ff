<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use common\models\Users;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\jui\AutoComplete;
use yii\helpers\Url;

$this->title = Yii::t('message', 'frontend.views.mercury.new_transport_vsd', ['ru' => 'Новый транспортный ВСД ']);
?>
<section class="content-header">
    <h1 class="margin-right-350">
        <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.mercury.new_transport_vsd_create', ['ru' => 'Создание нового транспортного ВСД']) ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru' => 'Интеграция']),
                'url'   => ['/clientintegr/default'],
            ],
            Yii::t('message', 'frontend.views.mercury.new_transport_vsd_step_four', ['ru' => 'Шаг 4. Создание нового транспортного ВСД'])
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body">
            <div class="panel-body">
                <div class="callout callout-fk-info">
                    <p>Подтвердите выполнение условий регионализации</p>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'StockEntryForm']); ?>
                <?= $form->field($model, 'type', ['enableClientValidation' => false])->hiddenInput()->label(false); ?>
                <?= $form->field($model, 'type_name')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'car_number')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'trailer_number')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'container_number')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'storage_type')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'mode')->hiddenInput()->label(false) ?>

                <?php
                echo $form->field($model, 'conditionsDescription')->hiddenInput()->label(false);
                $conditions = json_decode($model->conditionsDescription, true);
                  $style= <<< CSS
   .checkbox {
    margin-top: 0px;
    margin-bottom: 0px;
}
CSS;
        $this->registerCss($style);

        echo "<h4>Подтвердите выполнение условий регионализации: </h4>";
        $conditions = json_decode($model->conditionsDescription, true);
        /*echo "<pre>";
        var_dump($conditions); die();*/
       foreach ($conditions as $product => $data) {
        $step = 0;
        echo "<p><b><u>".$product."</u></b></p>";
        echo "<div style=\"-webkit-border-radius: 3px;border-radius: 3px;padding: 10px;border: 1px solid #ddd;\">";
        echo " <div style=\"padding-bottom: 15px;\">";
        foreach ($data as $key => $block) {
            if($step > 0) {
                echo "<p><b>и</b></p>";
            }
            echo "<div style=\"-webkit-border-radius: 3px;border-radius: 3px;padding: 10px;border: 1px solid #ddd;\">";
            echo "<p><b><u>".$key."</u></b></p>";
            $i = 0;
            foreach ($block as $item) {
                if($i > 0) {
                    echo "<p><b>или</b></p>";
                }
                echo $form->field($model, "conditions[$product]",['template' => '{input}{error}'])
                    ->checkbox([
                        'label' => $item['text'],
                        //'name' => "conditions[$product]",
                        'labelOptions' => [
                            'style' => 'padding-left:20px;'
                        ],
                        'value' => $item['guid']
                    ]);
                $i++;
            }
            echo "</div>";
            $step++;
        }
        echo "</div></div>";
    } ?>
            </div>
            <div class="form-group">
                <?php echo Html::submitButton(Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => 'btn btn-success']) ?>
                <?php
                if($model->mode == \frontend\modules\clientintegr\modules\merc\models\transportVsd\step4Form::CONFIRM_MODE) {
                    echo Html::button(Yii::t('message', 'frontend.views.layouts.client.integration.check_all', ['ru' => 'Выделить все']), ['class' => 'btn btn-primary', 'id'=>"select_all_conditions"]);
                }
                ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    </div>
</section>

<?php
$customJs = <<< JS
    $("#select_all_conditions").click( function()
          {
              $('#act_form input[type="checkbox"]').prop('checked', true);
           }
        );
JS;
$this->registerJs($customJs, \yii\web\View::POS_END);
?>

