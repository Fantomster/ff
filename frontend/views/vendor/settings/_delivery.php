<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use kartik\checkbox\CheckboxX;
use kartik\money\MaskMoney;
?>
<div class="col-md-8">
    <div class="box box-info">
        <div class="box-header with-border">
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
                        'template' => '{label}<div class="col-sm-5">{input}</div><div class="col-sm-10 pull-right">{error}</div>',
                        'labelOptions' => ['class' => 'col-sm-5 control-label'],
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
            <label class="col-sm-5 control-label">Дни доставки</label>
            <div class="col-sm-6">
                <!--?=
                        $form->field($delivery, 'delivery_mon')
                        ->label(false)
                        ->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'pluginOptions' => ['threeState' => false],
                        ])
                ?-->
                <?php
                echo CheckboxX::widget([
                    'name' => 'Delivery[delivery_mon]', 
                    'id' => 'mon', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->delivery_mon,
                    ]);
                echo '<label class="control-label" for="Delivery[delivery_mon]">Пнд</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[delivery_tue]', 
                    'id' => 'tue', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->delivery_tue,
                    ]);
                echo '<label class="control-label" for="Delivery[delivery_tue]">Втр</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[delivery_wed]', 
                    'id' => 'wed', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->delivery_wed,
                    ]);
                echo '<label class="control-label" for="Delivery[delivery_wed]">Срд</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[delivery_thu]', 
                    'id' => 'thu', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->delivery_thu
                    ]);
                echo '<label class="control-label" for="Delivery[delivery_thu]">Чтв</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[delivery_fri]', 
                    'id' => 'fri', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->delivery_fri,
                    ]);
                echo '<label class="control-label" for="Delivery[delivery_fri]">Птн</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[delivery_sat]', 
                    'id' => 'sat', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->delivery_sat,
                    ]);
                echo '<label class="control-label" for="Delivery[delivery_sat]">Сбт</label>';
                echo CheckboxX::widget([
                    'name' => 'Delivery[delivery_sun]', 
                    'id' => 'sun', 
                    'pluginOptions' => ['threeState' => false],
                    'value' => $delivery->delivery_sun,
                    ]);
                echo '<label class="control-label" for="Delivery[delivery_sun]">Вск</label>';
                ?>
                <!--
                <?=
                        $form->field($delivery, 'delivery_tue')
                        ->label(false)
                        ->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'pluginOptions' => ['threeState' => false]
                        ])
                ?>
                <?=
                        $form->field($delivery, 'delivery_wed')
                        ->label(false)
                        ->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'pluginOptions' => ['threeState' => false]
                        ])
                ?>
                <?=
                        $form->field($delivery, 'delivery_thu')
                        ->label(false)
                        ->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'pluginOptions' => ['threeState' => false]
                        ])
                ?>
                <?=
                        $form->field($delivery, 'delivery_fri')
                        ->label(false)
                        ->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'pluginOptions' => ['threeState' => false]
                        ])
                ?>
                <?=
                        $form->field($delivery, 'delivery_sat')
                        ->label(false)
                        ->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'pluginOptions' => ['threeState' => false]
                        ])
                ?>
                <?=
                        $form->field($delivery, 'delivery_sun')
                        ->label(false)
                        ->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'pluginOptions' => ['threeState' => false]
                        ])
                ?>-->
            </div>
        </div>
        <div class="box-footer">
            <?= Html::button('Отменить изменения', ['class' => 'btn btn-danger', 'id' => 'cancelDlv', 'disabled' => true]) ?>
            <?= Html::button('Сохранить изменения', ['class' => 'btn btn-primary pull-right', 'id' => 'saveDlv', 'disabled' => true]) ?>
        </div>				
        <?php ActiveForm::end(); ?>
    </div>
</div>