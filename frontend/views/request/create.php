<?php
 
use yii\helpers\Html;
use yii\helpers\BaseHtml;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use kartik\select2\Select2;
kartik\select2\Select2Asset::register($this);
use kartik\checkbox\CheckboxX;
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
?>
<style>
#create .modal-body {background: none;}
#create .modal-content .modal-header {display:none;}
#create .modal-fs .modal-body {
    top: 0;
}
#create .modal-content{background: url(/images/request-background.png) no-repeat center center;background-size: cover;}
#msform {width: 100%;margin: 11px auto;text-align: center;position: relative;}
#msform fieldset {padding: 20px 30px;min-height: 300px;box-sizing: border-box;border-radius: 4px;background: #fff;border: 5px solid #86be79;position: absolute;}
@media (min-width: 768px) {#msform fieldset {width: 60%;margin: 0 20%;}}
@media (max-width: 769px) {#msform fieldset {width: 95%;margin: 0 2.5%;}}

#msform fieldset:not(:first-of-type) {display: none;}
#progressbar {padding-left: 0;position: relative; z-index:1;margin-bottom: 30px;overflow: hidden;counter-reset: step;}
#progressbar li {list-style-type: none;color: #555;text-transform: uppercase;font-size: 11px;font-weight: 500;width: 33.3333%;float: left;position: relative;}
#progressbar li:before {content: counter(step); counter-increment: step; width: 75px; line-height: 75px; display: block; font-size: 0px; color: #fff; background: #3f3e3e; border-radius: 50%; margin: 0 auto 5px auto;}
#progressbar li.active{color:#86be79;}
#progressbar li:after {content: ''; width: 100%; height: 5px; background: #3f3e3e; position: absolute; left: -50%; top: 35px; z-index: -1;}
#progressbar li:first-child:after {content: none; }
#progressbar li.active:before, #progressbar li.active:after{background: #84bf76;color: white;}
#msform .btn{border-radius:3px;}
#msform .btn-success{border-color: #84bf76;color: #84bf76}
#msform .btn-success:hover,.btn-success:active,.btn-success:focus{background: #84bf76;color: #fff;border-color: #84bf76}
#msform .btn-outline { background-color: transparent; color: inherit; transition: all .5s;}
#msform .btn-default.btn-outline {background: #fff;color: #c1c1c1;border-color: #ccc}
#msform .btn-default.btn-outline:hover,.btn-default.btn-outline:active,.btn-default.btn-outline:focus{background: #c1c1c1;color: #fff;border-color: #c1c1c1}
#msform .btn-primary.btn-outline { color: #428bca;}
#msform .btn-success.btn-outline { color: #5cb85c;}
#msform .btn-info.btn-outline { color: #5bc0de;}
#msform .btn-warning.btn-outline { color: #f0ad4e;}
#msform .btn-danger.btn-outline { color: #d9534f;}
#msform .btn-primary.btn-outline:hover,.btn-success.btn-outline:hover,.btn-info.btn-outline:hover,.btn-warning.btn-outline:hover,.btn-danger.btn-outline:hover { color: #fff;}
.overlay-white{width: 100%;height: 100%;top:0;left:0;background: rgba(255,255,255,.5);position: fixed;z-index: -1;}
.control-group { display: inline-block; vertical-align: top; background: #fff; text-align: left; box-shadow: 0 1px 2px rgba(0,0,0,0.1); padding: 30px; width: 200px; height: 210px; margin: 10px;}
.control { display: block; position: relative; padding-left: 40px; margin-bottom: 15px;margin-top:20px; padding-top: 6px; cursor: pointer; font-size: 18px;}
.control input { position: absolute; z-index: -1; opacity: 0;}
.control__indicator { position: absolute; top: 2px; left: 0; height: 30px; width: 30px; border: 3px solid #ccc; border-radius:4px;}
.control--radio .control__indicator { border-radius: 50%;}
.control input:checked ~ .control__indicator { background: none; border: 3px solid #86be76;}
.control:hover input:not([disabled]):checked ~ .control__indicator,.control input:checked:focus ~ .control__indicator { border: 3px solid #86be79;}
.control input:disabled ~ .control__indicator { border: 3px solid #86be76; opacity: 0.6; pointer-events: none;}
.control__indicator:after { content: ''; position: absolute; display: none;}
.control input:checked ~ .control__indicator:after { display: block;}
.control--checkbox .control__indicator:after {left: 9px; top: 3px; width: 8px; height: 15px; border: solid #86be79; border-width: 0 4px 4px 0; transform: rotate(45deg);}
.control--checkbox input:disabled ~ .control__indicator:after { border-color: #7b7b7b;}
.control--radio .control__indicator:after { left: 7px; top: 7px; height: 6px; width: 6px; border-radius: 50%; background: #fff;}
.control--radio input:disabled ~ .control__indicator:after { background: #7b7b7b;}
.select { position: relative; display: inline-block; margin-bottom: 15px; width: 100%;}
.select select { display: inline-block; width: 100%; cursor: pointer; padding: 10px 15px; outline: 0; border: 0; border-radius: 0; background: #e6e6e6; color: #7b7b7b; appearance: none; -webkit-appearance: none; -moz-appearance: none;}
.select select::-ms-expand { display: none;}
.select select:hover,.select select:focus { color: #000; background: #ccc;}
.select select:disabled { opacity: 0.5; pointer-events: none;}
.select__arrow { position: absolute; top: 16px; right: 15px; width: 0; height: 0; pointer-events: none; border-style: solid; border-width: 8px 5px 0 5px; border-color: #7b7b7b transparent transparent transparent;}
.select select:hover ~ .select__arrow,.select select:focus ~ .select__arrow { border-top-color: #000;}
.select select:disabled ~ .select__arrow { border-top-color: #ccc;}
#msform .f-title{font-size:32px;}
#msform .text-small{color: #ccc;font:normal 20px;}
#msform fieldset input[type=text]{border: 0;border-radius: 0;border-bottom: 2px solid #ccc;box-shadow: none;padding: 0px 0px 0px 0px;font-size: 22px; color: #999}
#msform fieldset ::-webkit-input-placeholder { color:#efefef; font-size: 22px; letter-spacing: .05em}
#msform fieldset ::-moz-placeholder { color:#efefef;  font-size: 22px; letter-spacing: .05em}
#msform fieldset :-ms-input-placeholder { color:#efefef;  font-size: 22px; letter-spacing: .05em}
#msform fieldset input:-moz-placeholder { color:#efefef;  font-size: 22px; letter-spacing: .05em}
#msform fieldset h5{margin-top: 24px;font-size: 20px;color:#3f3e3e}
#msform fieldset {padding-left:10%; padding-right: 10%;}
#progressbar{margin-top: 40px;}
#msform fieldset > .btn {font-size: 18px; font:bold;border-width: 3px; border-radius:4px;}
#msform .tooltip {border-radius: 3px;font:400 12px;}
#msform .tooltip > .tooltip-inner {background-color: #fefefe;border: 1px solid #555;color:#555;padding: 15px 18px;text-align: left;max-width: 600px;width: 400px}
#msform .form-close{position: absolute;top:0px;right: 0px;cursor: pointer;z-index: 99}
#msform li .li-text{position: absolute;top:30px;left:0;right:0;color:white}
.mswrapper{width: 80%;margin: 0 10%;}
.close-h{margin-top: 56px;font-size: 18px;color: #ccc;}
.select2-container--krajee.select2-container--open .select2-selection, .select2-container--krajee .select2-selection:focus {box-shadow: none;}
.modal-fs .select2-container--krajee .select2-selection {border: none;border-bottom: 2px solid #ccc;border-radius: 0;-webkit-box-shadow: none;box-shadow: none;height: 33px;margin-top:23px;}
.select2-container--krajee .select2-selection--single {height: 34px;font-size:22px;padding: 0;}
.select2-container--krajee .select2-selection--single .select2-selection__placeholder {color: #efefef;font-size:22px;letter-spacing: .05em;}
.select2-container--krajee .select2-selection--single .select2-selection__rendered {color: #999;padding: 0;}
.select2-container--krajee .select2-selection__clear {float: right;color: #000;cursor: pointer;font-size: 18px;font-weight: 700;line-height: 1.7;opacity: 0.4;filter: alpha(opacity=40);position: absolute;right: 0;margin-right: 20px;}
@media (max-width: 769px) {
.mswrapper, #progressbar{display:none}
#msform .f-title{font:bold 24px;}
#msform .text-small{color: #ccc;font:normal 14px;}
#msform fieldset input[type=text]{border: 0;border-radius: 0;border-bottom: 2px solid #ccc;box-shadow: none;padding: 0px 0px 0px 0px;font-size: 22px; color: #999}
#msform fieldset ::-webkit-input-placeholder { color:#efefef; font-size: 18px; letter-spacing: .01em}
#msform fieldset ::-moz-placeholder { color:#efefef;  font-size: 18px; letter-spacing: .01em}
#msform fieldset :-ms-input-placeholder { color:#efefef;  font-size: 18px; letter-spacing: .01em}
#msform fieldset input:-moz-placeholder { color:#efefef;  font-size: 18px; letter-spacing: .01em}
#msform fieldset h5{margin-top: 24px;font-size: 16px;color:#3f3e3e}
.select2-container--krajee .select2-selection--single {height: 34px;font-size:16px;padding: 0;}
.select2-container--krajee .select2-selection--single .select2-selection__placeholder {color: #efefef;font-size:18px;letter-spacing: .01em;}
}
#msform fieldset button[type=button]{margin-top: 43px;margin-bottom: 20px}
@media (max-width: 769px) {
#msform fieldset button[type=button]{margin-top: 15px;margin-bottom: 5px;width: 100%;}
}
#msform fieldset button[type=submit]{margin-top: 43px;margin-bottom: 20px}
@media (max-width: 769px) {
#msform fieldset button[type=submit]{margin-top: 15px;margin-bottom: 5px;width: 100%;}
}


.select2-container--krajee .select2-dropdown {
    -webkit-box-shadow: none;
    box-shadow: none;
    border-color: #ccc;
    background:#fefefe;
}
.select2-container--krajee .select2-results__option[aria-selected] {
    background-color: #fefefe;
}
.has-success .select2-container--krajee.select2-container--focus .select2-selection {
    -webkit-box-shadow: none;
    box-shadow: none;
    border-color:#ccc;
}
.cbx-krajee-flatblue .cbx-active:hover, .cbx-krajee-flatblue .cbx-active:focus {
    border-color: #86be79;
}
.cbx-krajee-flatblue .cbx-active {
    color: #86be79 !important;
}
.has-success .cbx-krajee-flatblue .cbx-active {
    border-color: #86be79 !important;
    color: #86be79;   
}
.form-group.has-success label {
    color: #86be79;
}
.field-request-rush_order{margin-top:20px}
.field-profile-sms_allow {
    margin-top: 20px;
}
.pac-container { z-index: 9999 !important; }
#map{width:100%;height:250px;}
.clear-input{
    top: 9px;
    float: right;
    color: #999;
    cursor: pointer;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.7;
    filter: alpha(opacity=40);
    position: absolute;
    right: 0;
    margin-right: 5px;}
[class^="flaticon-"]:before, [class*=" flaticon-"]:before, [class^="flaticon-"]:after, [class*=" flaticon-"]:after{font-size:40px}
</style>
<?php 
Pjax::begin([
  'id' => 'pjax-create', 
  'timeout' => 10000, 
  'enablePushState' => false,
  ]);
?>
<?php $form = ActiveForm::begin([
        'id' => 'msform',
        'enableAjaxValidation' => true,
        'validationUrl' => Url::toRoute('request/save-request')

]); ?>
        <div class="mswrapper" style="position: relative;height: 80px">
                <i class="flaticon-cross-out form-close" data-dismiss="modal" style="top: 10px;"></i>
		<div style="display: inline-block;position: absolute;left:0">
		<h4 class="f-title"><?= Yii::t('message', 'frontend.views.request.set_req_seven', ['ru'=>'Разместить заявку']) ?></h4>
		<h4 class="text-small text-left" data-toggle="tooltip" data-placement="right" title="<?= Yii::t('message', 'frontend.views.request.for_example_two', ['ru'=>'Например, вам срочно нужен какой-то товар, но у Ваших поставщиков его нет в наличии...что делать? Размещайте заявку на необходимый товар и вас увидят все поставщики системы MixCart! Это отличная возможность преобрести Честного партнера на долгосрочное сотрудничество!']) ?>"><?= Yii::t('message', 'frontend.views.request.what_is_it', ['ru'=>'Что такое разместить заявку?']) ?></h4>
		</div>
	</div>
	<!-- progressbar -->
	<ul id="progressbar">
		<li class="active"><span class="li-text"><?= Yii::t('message', 'frontend.views.request.product_two', ['ru'=>'Продукт']) ?></span></li>
		<li><span class="li-text"><?= Yii::t('message', 'frontend.views.request.conditions_two', ['ru'=>'Условия']) ?></span></li>
		<li><span class="li-text"><?= Yii::t('message', 'frontend.views.request.to_end', ['ru'=>'Завершить']) ?></span></li>
                <!--li><span class="li-text">Контакты</span></li-->
	</ul>
	<!-- fieldsets -->
	<fieldset class="text-left">
            <span <?php if($organization->place_id && $organization->address){ echo "style='display:none'"; }?>>
                <h5><?= Yii::t('message', 'frontend.views.request.org_address', ['ru'=>'Адрес организации']) ?><span style="font-size:24px;color:#dd4b39;margin-left:5px" title="<?= Yii::t('message', 'frontend.views.request.required_field_four', ['ru'=>'Обязательное поле']) ?>">*</span></h5>
                <?= $form->field($organization, 'address',['template'=>'<div style="position:relative">{input}<span class="clear-input">×</span></div>{error}'])->textInput(['maxlength' => 255,'style'=>'padding-right:22px'])->label(false) ?>
                <div id="map"></div>
            </span>
            
<?= Html::activeHiddenInput($organization, 'lat'); //широта ?>
<?= Html::activeHiddenInput($organization, 'lng'); //долгота ?>
<?= Html::activeHiddenInput($organization, 'country'); //страна ?> 
<?= Html::activeHiddenInput($organization, 'locality'); //Город ?>
<?= Html::activeHiddenInput($organization, 'administrative_area_level_1'); //область ?>
<?= Html::activeHiddenInput($organization, 'route'); //улица ?>
<?= Html::activeHiddenInput($organization, 'street_number'); //дом ?>
<?= Html::activeHiddenInput($organization, 'place_id'); //уникальный индификатор места ?>
<?= Html::activeHiddenInput($organization, 'formatted_address'); //полный адрес ?>
<?php
$gpJsLink= 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
        'libraries' => 'places',
        'key'=>Yii::$app->params['google-api']['key-id'],
        'language'=>Yii::$app->params['google-api']['language'],
        'callback'=>'initMap'
    ));
$this->registerJsFile($gpJsLink, ['depends' => [yii\web\JqueryAsset::className()],'async'=>true, 'defer'=>true]);
$this->registerJs("
$(document).on('keyup change', '#organization-address', function(){
$(this).next().toggle(Boolean($(this).val()));
    });
    $('.clear-input').toggle(Boolean($('#organization-address').val()));
    $('.clear-input').click(function () {
        $(this).prev().val('').focus();
        $(this).hide();
})

",yii\web\View::POS_END);
?>
            <h5><?= Yii::t('message', 'frontend.views.request.choose_category', ['ru'=>'Выберите категорию товара']) ?><span style="font-size:24px;color:#dd4b39;margin-left:5px" title="<?= Yii::t('message', 'frontend.views.request.required_field_five', ['ru'=>'Обязательное поле']) ?>">*</span></h5>
            <?php 
            echo $form->field($request, 'category',['template'=>'{input}{error}'])->widget(Select2::classname(), [
                'model'=>$request->category,
                'data' => ArrayHelper::map(\common\models\MpCategory::find()->where(['parent'=>null])->orderBy('name')->all(),'id','name'),
                'options' => ['placeholder' => Yii::t('message', 'frontend.views.request.meat_two', ['ru'=>'Мясо'])],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label(false);
            ?>
            <h5><?= Yii::t('message', 'frontend.views.request.what_wanna_buy', ['ru'=>'Что вы хотите купить?']) ?><span style="font-size:24px;color:#dd4b39;margin-left:5px" title="<?= Yii::t('message', 'frontend.views.request.required_field_six', ['ru'=>'Обязательное поле']) ?>">*</span></h5>
            <?= $form->field($request, 'product', 
    ['template'=>'{input}{error}'])->
    textInput(['placeholder' => Yii::t('message', 'frontend.views.request.tomatos_two', ['ru'=>'Помидоры Азербайджанские'])]) ?>
            <h5><?= Yii::t('message', 'frontend.views.request.order_comment_two', ['ru'=>'Комментарий к заказу']) ?></h5>
            <?= $form->field($request, 'comment', 
    ['template'=>'{input}{error}'])->
    textInput(['placeholder' => '']) ?>
            <?= Html::button(Yii::t('message', 'frontend.views.request.continue_four', ['ru'=>'Продолжить']), ['class' => 'next btn btn-lg btn-success btn-outline','data-step'=>1]) ?>
            <a href="#" data-dismiss="modal" class="close-h pull-right"><?= Yii::t('message', 'frontend.views.request.back_to_list_three', ['ru'=>'Вернуться к списку заявок']) ?></a>
        </fieldset>
        <!-- fieldsets 2 -->
	<fieldset class="text-left">
            
            <h5><?= Yii::t('message', 'frontend.views.request.how_often_two', ['ru'=>'Как часто?']) ?></h5>
            <?php 
            echo $form->field($request, 'regular',['template'=>'{input}{error}'])->widget(Select2::classname(), [
                'model'=>$request->regular,
                'hideSearch' => true,
                'data' => [1=>Yii::t('message', 'frontend.views.request.once_two', ['ru'=>'Разово']),2=>Yii::t('message', 'frontend.views.request.daily_two', ['ru'=>'Ежедневно']),3=>Yii::t('message', 'frontend.views.request.weekly_two', ['ru'=>'Каждую неделю']),4=>Yii::t('message', 'frontend.views.request.monthly_two', ['ru'=>'Каждый месяц'])],
                //'options' => ['placeholder' => 'Разово'],
//                'pluginOptions' => [
//                    'allowClear' => true
//                ],
            ])->label(false);
            ?>
            <h5><?= Yii::t('message', 'frontend.views.request.buying_value_four', ['ru'=>'Объем закупки?']) ?><span style="font-size:24px;color:#dd4b39;margin-left:5px" title="<?= Yii::t('message', 'frontend.views.request.required_field_seven', ['ru'=>'Обязательное поле']) ?>">*</span></h5>
            <?= $form->field($request, 'amount', 
    ['template'=>'{input}{error}'])->
    textInput(['placeholder' => Yii::t('message', 'frontend.views.request.fifteen_two', ['ru'=>'15 кг'])]) ?>
            <?=$form->field($request, 'rush_order')->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'model' => $request,
                            'attribute' => 'rush_order',
                            'pluginOptions'=>[
                                'threeState'=>false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => false,
                                'size'=>'lg',
                                ],
                            'labelSettings' => [
                                'label' => Yii::t('message', 'frontend.views.request.urgent_order_two', ['ru'=>'Срочный заказ']) . '  <span style="font-size:14px;color:#ccc;margin-left:5px">' . Yii::t('message', 'frontend.views.request.twenty_four_two', ['ru'=>'доставить в течении 24 часов']) . ' </span>',
                                'position' => CheckboxX::LABEL_RIGHT,
                                'options' =>['style'=>'font-size: 20px;color: #3f3e3e;font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;font-weight: 500;']
                                ]
                            ])->label(false);?>
            <?= Html::button(Yii::t('message', 'frontend.views.request.back_three', ['ru'=>'Назад']), ['class' => 'previous btn btn-lg btn-default btn-outline']) ?>
            <?= Html::button(Yii::t('message', 'frontend.views.request.continue_five', ['ru'=>'Продолжить']), ['class' => 'next btn btn-lg btn-success btn-outline','data-step'=>2]) ?>
            <a href="#" data-dismiss="modal" class="close-h pull-right"><?= Yii::t('message', 'frontend.views.request.back_to_list_four', ['ru'=>'Вернуться к списку заявок']) ?></a>
        </fieldset>
        <!-- fieldsets 3 -->
	<fieldset class="text-left">
            <h5><?= Yii::t('message', 'frontend.views.request.payment_variant_three', ['ru'=>'Способ оплаты?']) ?></h5>
            <?php 
            echo $form->field($request, 'payment_method',['template'=>'{input}{error}'])->widget(Select2::classname(), [
                'model'=>$request->payment_method,
                'hideSearch' => true,
                'data' => [1=>Yii::t('message', 'frontend.views.request.cash_two', ['ru'=>'Наличный расчет']),2=>Yii::t('message', 'frontend.views.request.no_cash_two', ['ru'=>'Безналичный расчет'])],
            ])->label(false);
            ?>
            
		<h5><?= Yii::t('message', 'frontend.views.request.postponement_two', ['ru'=>'Желаемая отсрочка платежа?']) ?></h5>
                <?= $form->field($request, 'deferment_payment', 
    ['template'=>'{input}{error}'])->
    textInput(['placeholder' => Yii::t('message', 'frontend.views.request.seven_days', ['ru'=>'7 дней'])]) ?>
            
		<!--h5>Поделиться заявкой в группах MixCart</h5-->
                <div style="color:#ccc;font-size:13px;margin-top: 32px;margin-bottom: -32px;">
                    * <?= Yii::t('message', 'frontend.views.request.request_will_be', ['ru'=>'Заявка будет существовать в системе MixCart один месяц, или пока вы ее не закроете']) ?>
                </div>
                <?= Html::button(Yii::t('message', 'frontend.views.request.back_four', ['ru'=>'Назад']), ['class' => 'previous btn btn-lg btn-default btn-outline']) ?>
                <?= Html::button(Yii::t('message', 'frontend.views.request.set_req_eight', ['ru'=>'Разместить заявку']), ['class' => 'next btn btn-lg btn-success btn-outline','data-step'=>3]) ?>
        <a href="#" data-dismiss="modal" class="close-h pull-right"><?= Yii::t('message', 'frontend.views.request.back_to_list_five', ['ru'=>'Вернуться к списку заявок']) ?></a>
        </fieldset>
<?php ActiveForm::end(); ?>

<?php Pjax::end(); ?>
