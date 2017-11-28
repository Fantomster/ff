<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\checkbox\CheckboxX;
use yii\widgets\Pjax;
$this->title = Yii::t('message', 'frontend.views.vendor.settings_two', ['ru'=>'Настройки']);
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
$this->registerCss('
section>h3>small {
    font-size: 15px;
    display: inline-block;
    padding-left: 4px;
    font-weight: 300;
}
');
?>
<section class='content-header'>
    <h1>
        <i class="fa fa-gears"></i> <?= Yii::t('message', 'frontend.views.vendor.delivery', ['ru'=>'Доставка']) ?>
        <small><?= Yii::t('message', 'frontend.views.vendor.deliv_cond', ['ru'=>'Настройки условий доставки']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            Yii::t('message', 'frontend.views.vendor.settings_three', ['ru'=>'Настройки']),
            Yii::t('message', 'frontend.views.vendor.cust_two', ['ru'=>'Общие']),
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
                <label class="col-sm-4 control-label"><?= Yii::t('message', 'frontend.views.vendor.deliv_days', ['ru'=>'Дни доставки']) ?></label>
                <div class="col-sm-5">
                    <?php
                    echo CheckboxX::widget([
                        'name' => 'Delivery[mon]',
                        'id' => 'mon',
                        'pluginOptions' => ['threeState' => false],
                        'value' => $delivery->mon,
                    ]);
                    echo '<label class="control-label" for="Delivery[mon]">' . Yii::t('message', 'frontend.views.vendor.mon', ['ru'=>'Пн']) . ' </label>';
                    echo CheckboxX::widget([
                        'name' => 'Delivery[tue]',
                        'id' => 'tue',
                        'pluginOptions' => ['threeState' => false],
                        'value' => $delivery->tue,
                    ]);
                    echo '<label class="control-label" for="Delivery[tue]">' . Yii::t('message', 'frontend.views.vendor.tue', ['ru'=>'Вт']) . ' </label>';
                    echo CheckboxX::widget([
                        'name' => 'Delivery[wed]',
                        'id' => 'wed',
                        'pluginOptions' => ['threeState' => false],
                        'value' => $delivery->wed,
                    ]);
                    echo '<label class="control-label" for="Delivery[wed]">' . Yii::t('message', 'frontend.views.vendor.wed', ['ru'=>'Ср']) . ' </label>';
                    echo CheckboxX::widget([
                        'name' => 'Delivery[thu]',
                        'id' => 'thu',
                        'pluginOptions' => ['threeState' => false],
                        'value' => $delivery->thu
                    ]);
                    echo '<label class="control-label" for="Delivery[thu]">' . Yii::t('message', 'frontend.views.vendor.thu', ['ru'=>'Чт']) . ' </label>';
                    echo CheckboxX::widget([
                        'name' => 'Delivery[fri]',
                        'id' => 'fri',
                        'pluginOptions' => ['threeState' => false],
                        'value' => $delivery->fri,
                    ]);
                    echo '<label class="control-label" for="Delivery[fri]">' . Yii::t('message', 'frontend.views.vendor.fri', ['ru'=>'Пт']) . ' </label>';
                    echo CheckboxX::widget([
                        'name' => 'Delivery[sat]',
                        'id' => 'sat',
                        'pluginOptions' => ['threeState' => false],
                        'value' => $delivery->sat,
                    ]);
                    echo '<label class="control-label" for="Delivery[sat]">' . Yii::t('message', 'frontend.views.vendor.sat', ['ru'=>'Сб']) . ' </label>';
                    echo CheckboxX::widget([
                        'name' => 'Delivery[sun]',
                        'id' => 'sun',
                        'pluginOptions' => ['threeState' => false],
                        'value' => $delivery->sun,
                    ]);
                    echo '<label class="control-label" for="Delivery[sun]">' . Yii::t('message', 'frontend.views.vendor.sun', ['ru'=>'Вс']) . ' </label>';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="box-footer">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.save_changes', ['ru'=>'Сохранить изменения']) . ' ', ['class' => 'btn btn-success', 'id' => 'saveDlv', 'disabled' => true]) ?>
            <?= Html::button('<i class="icon fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.vendor.cancel', ['ru'=>'Отменить изменения']) . ' ', ['class' => 'btn btn-gray', 'id' => 'cancelDlv', 'disabled' => true]) ?>
        </div>				
        <?php
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>    
</section>
<section class='content'>
    <h3>
        <i class="fa fa-gears"></i> <?= Yii::t('message', 'frontend.views.vendor.regions', ['ru'=>'Регионы доставки']) ?>
        <small>Добавьте регионы в которые Вы доставляете</small>
    </h3>
    <div class="box box-info delivery">
        <div class="box-header">
        </div>
        <div class="box-body">
            <div class="row" id="forma">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Поиск</label>
                            <input type="text" class="form-control autocomplete" id="search_in" name="search_in" placeholder="Поиск">    
                        </div>
                    </div>
                    <?php
                    $form = ActiveForm::begin([
                        'options' => [
                            'id' => 'form-in'
                        ],
                    ]);
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <?= $form->field($deliveryRegions, 'country')->textInput()?> 
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($deliveryRegions, 'administrative_area_level_1')->textInput()?> 
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($deliveryRegions, 'locality')->textInput()?>
                                </div>
                                <div class="col-md-12">
                                    <?= $form->field($deliveryRegions, 'exception')->hiddenInput(['value' => 0])->label(false);?>
                                    <button type="submit" class="save btn btn-success">Добавить</button>  
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    ActiveForm::end(); 
                    ?>
                </div>
                <br>
                <div class="col-md-12">
                    <div class="row">
                        <?php Pjax::begin(['id'=>'pjax-container-form']); ?>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Страна</strong>
                                </div>
                                <div class="col-md-4">
                                    <strong>Область</strong>
                                </div>
                                <div class="col-md-4">
                                    <strong>Город</strong>
                                </div>
                                <hr>
                            </div>

                            <?php foreach($regionsList as $list){ ?>
                            <?php if($list->exception == 0){ ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <?=$list->country;?>
                                        </div>
                                        <div class="col-md-4">
                                            <?=$list->administrative_area_level_1;?>
                                        </div>
                                        <div class="col-md-3">
                                            <?=$list->locality;?>
                                        </div>
                                        <div class="col-md-1">
                                            <a href="#" class="delete btn btn-sm btn-danger" style="margin:1px 0;" data-url="<?=Url::toRoute(['vendor/remove-delivery-region', 'id' => $list->id]);?>">-</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <?php } } ?>
                        </div>
                        <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>
            <br>
            
        </div>
    </div>
</section>
<script type="text/javascript"> 
function stopRKey(evt) { 
var evt = (evt) ? evt : ((event) ? event : null); 
var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
if ((evt.keyCode == 13) && (node.type=="text")) {return false;} 
} 
document.onkeypress = stopRKey; 
</script>
<?php
use frontend\assets\GoogleMapsRegionsAsset;
GoogleMapsRegionsAsset::register($this);
?>
<?php
$this->registerJs('
$(document).ready(
    $("#form-in").on("beforeSubmit", function(event, jqXHR, settings) {
        var form = $(this);
        $(".save").prop("disabled", true);
        if(form.find(".has-error").length) {
            return false;
        }
        console.log(form.serialize())
        $.ajax({
            url: form.attr("action"),
            type: "post",
            data: form.serialize(),
            success: function(data) {
                $(".save").prop("disabled", false);
                $("#form-in input[type=\'text\']").val("")
                $.pjax.reload({container: "#pjax-container-form"});
            }
        });
        return false;
    }),
    $("#form-out").on("beforeSubmit", function(event, jqXHR, settings) {
        var form = $(this);
        $(".save").prop("disabled", true);
        if(form.find(".has-error").length) {
            return false;
        }
        $.ajax({
            url: form.attr("action"),
            type: "post",
            data: form.serialize(),
            success: function(data) {
                $(".save").prop("disabled", false);
                $("#form-out input[type=\'text\']").val("")
                $.pjax.reload({container: "#pjax-container-form"});
            }
        });
        return false;
    }),
);

    $(document).on("click", ".delete", function(e){
    e.preventDefault()
    var url = $(this).attr("data-url");
    var r = confirm("Подтвердите удаление! ");
    if (r == true) {
        $.ajax({
                url: url,
                type: "get",
                success: function(data) {
                    $.pjax.reload({container: "#pjax-container-form"});
                }
            });    
        }
    });
');
?>