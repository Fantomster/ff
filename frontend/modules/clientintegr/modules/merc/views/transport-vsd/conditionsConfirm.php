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
                <?php $form = ActiveForm::begin(['id' => 'StockEntryForm']);
                $model->confirm = true;
                ?>
                <?= $form->field($model, 'type', ['enableClientValidation' => false])->hiddenInput()->label(false); ?>
                <?= $form->field($model, 'type_name')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'car_number')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'trailer_number')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'container_number')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'storage_type')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'confirm')->hiddenInput()->label(false) ?>

                <?php
                echo Html::hiddenInput('conditions', $conditions);
                $conditions = json_decode($conditions, true);
                 echo " <div style=\"padding-left: 15px;padding-right: 15px;\">";
                    foreach ($conditions as $key => $item) {
                        echo "<p style=\"font-weight: bold;text-decoration: underline;\">" . $key ."</p>";
                        foreach ($item as $cond) {
                            echo "<li style=\"padding-bottom: 10px;\">" . $cond . "</li>";
                        }
                        echo "</ul>";
                    }
                echo "</div>";
                ?>
            </div>
            <div class="form-group">
                <?php echo Html::submitButton('Подтвердить', ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    </div>
</section>
