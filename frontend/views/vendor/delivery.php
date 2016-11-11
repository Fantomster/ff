<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\checkbox\CheckboxX;
use yii\widgets\Pjax;

$this->registerJs(
        '$("document").ready(function(){
            $(".delivery").on("click", "#cancelDlv", function() {
                $.pjax.reload({container: "#settingsDelivery"});            
            });
            $(".delivery").on("change paste keyup", "input", function() {
                $("#cancelDlv").prop( "disabled", false );
                $("#saveDlv").prop( "disabled", false );
            });
        });'
);
?>
<section class='content-header'>
    <h1>
        <i class="fa fa-gears"></i> Доставка
        <small>Настройки условий доставки</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Настройки',
            'Общие',
        ],
    ])
    ?>
</section>
<section class='content'>
    <div class="box box-info delivery">
        <div class="box-header">
        </div>
        <?php
        Pjax::begin(['enablePushState' => false, 'id' => 'settingsDelivery', 'timeout' => 5000]);
        $form = ActiveForm::begin([
                    'id' => 'deliveryForm',
                    'enableAjaxValidation' => false,
                    'options' => [
                        'class' => 'form-horizontal',
                        'data-pjax' => true,
                    ],
                    'method' => 'get',
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
        <div class="box-body">
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
        </div>
        
        <div class="box-footer">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> Сохранить изменения', ['class' => 'btn btn-success', 'id' => 'saveDlv', 'disabled' => true]) ?>
            <?= Html::button('<i class="icon fa fa-ban"></i> Отменить изменения', ['class' => 'btn btn-gray', 'id' => 'cancelDlv', 'disabled' => true]) ?>
        </div>				
        <?php
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>    
</section>