<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use kartik\checkbox\CheckboxX;
?>
    <div class="box box-info">
        <div class="box-header">
        </div>
        <?php
        $form = ActiveForm::begin([
                    'id' => 'deliverySettings',
                    'enableAjaxValidation' => false,
                    'action' => Url::toRoute(['vendor/ajax-update-delivery']),
                    'validationUrl' => Url::toRoute('vendor/ajax-validate-delivery'),
                    'options' => [
                        'class' => 'form-horizontal'
                    ],
                    'fieldConfig' => [
                        'template' => '{label}<div class="col-sm-5">{input}</div><div class="col-sm-9 pull-right">{error}</div>',
                        'labelOptions' => ['class' => 'col-sm-3 control-label'],
                    ],
        ]);
        ?>

        <?=
                $form->field($delivery, 'delivery_charge')
                ->widget(\yii\widgets\MaskedInput::className(), [
                    'clientOptions' => [
                        'alias' => 'decimal',
                        'digits' => 2,
                        'digitsOptional' => false,
                        'autoGroup' => false,
                        'removeMaskOnSubmit' => true,
                        'rightAlign' => false,
                        ],
                ])
        ?>

        <?=
                $form->field($delivery, 'min_free_delivery_charge')
                ->widget(\yii\widgets\MaskedInput::className(), [
                    'clientOptions' => [
                        'alias' => 'decimal',
                        'digits' => 2,
                        'digitsOptional' => false,
                        'autoGroup' => false,
                        'removeMaskOnSubmit' => true,
                        'rightAlign' => false,
                        ],
                ])
        ?>

        <?=
                $form->field($delivery, 'min_order_price')
                ->widget(\yii\widgets\MaskedInput::className(), [
                    'clientOptions' => [
                        'alias' => 'decimal',
                        'digits' => 2,
                        'digitsOptional' => false,
                        'autoGroup' => false,
                        'removeMaskOnSubmit' => true,
                        'rightAlign' => false,
                        ],
                ])
        ?>
        <div class="form-group">
            <label class="col-sm-4 control-label">Дни доставки</label>
            <div class="col-sm-5">
                <?php
                echo CheckboxX::widget([
                    'name' => 'Delivery[mon]', 
                    'id' => 'mon', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->mon,
                    ]);
                echo '<label class="control-label" for="Delivery[mon]">Пн</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[tue]', 
                    'id' => 'tue', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->tue,
                    ]);
                echo '<label class="control-label" for="Delivery[tue]">Вт</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[wed]', 
                    'id' => 'wed', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->wed,
                    ]);
                echo '<label class="control-label" for="Delivery[wed]">Ср</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[thu]', 
                    'id' => 'thu', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->thu
                    ]);
                echo '<label class="control-label" for="Delivery[thu]">Чт</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[fri]', 
                    'id' => 'fri', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->fri,
                    ]);
                echo '<label class="control-label" for="Delivery[fri]">Пт</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[sat]', 
                    'id' => 'sat', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->sat,
                    ]);
                echo '<label class="control-label" for="Delivery[sat]">Сб</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[sun]', 
                    'id' => 'sun', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->sun,
                    ]);
                echo '<label class="control-label" for="Delivery[sun]">Вс</label>';
                ?>
            </div>
        </div>
        <div class="box-footer">
            <?= Html::button('Сохранить изменения', ['class' => 'btn btn-success', 'id' => 'saveDlv', 'disabled' => true]) ?>
            <?= Html::button('Отменить изменения', ['class' => 'btn btn-danger', 'id' => 'cancelDlv', 'disabled' => true]) ?>
        </div>				
        <?php ActiveForm::end(); ?>
    </div>