<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\widgets\Pjax;
use common\models\Users;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use kartik\widgets\Select2;
use yii\web\JsExpression;

$this->title = Yii::t('message', 'frontend.views.mercury.new_transport_vsd', ['ru'=>'Новый транспортный ВСД ']);
$urlToGetHC = \yii\helpers\Url::to(['get-hc']);
?>
<section class="content-header">
        <h1 class="margin-right-350">
            <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.mercury.new_transport_vsd_create', ['ru'=>'Создание нового транспортного ВСД']) ?>
        </h1>
        <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru'=>'Интеграция']),
                'url' => ['/clientintegr/default'],
            ],
            Yii::t('message', 'frontend.views.mercury.new_transport_vsd_step_three', ['ru'=>'Шаг 3. Создание нового транспортного ВСД'])
        ],
    ])
?>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-body">
            <div class="panel-body">
                <ul class="nav fk-tab nav-tabs  pull-left">
                    <?= '<li class="">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_select_product', ['ru'=>' Выбор продукции'])).'</li>';?>
                    <?= '<li class="">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_vet_info', ['ru'=>'Ветеринарная Экспертиза'])).'</li>';?>
                    <?= '<li class="active">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_recipient_info', ['ru'=>' Информация о товарополучателе']).' <i class="fa fa-fw fa-hand-o-right"></i>',['step-2'],['class'=>'btn btn-default']).'</li>'?>
                    <?= '<li class="">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_transport_info', ['ru'=>'Информация о транспорте'])).'</li>'?>
                </ul>
                <ul class="fk-prev-next pull-right">
                  <?= '<li class="fk-prev">' . Html::a(Yii::t('message', 'frontend.views.vendor.back', ['ru'=>'Назад']), ['step-2']) . '</li>' ?>
                  <?='<li class="fk-next">'.Html::a('<i class="fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.continue', ['ru'=>'Далее']) . ' ',['#'],['class' => 'step-4']).'</li>'?>
                </ul>
        </div>
        <?php Pjax::begin(['id' => 'pjax-container'])?>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4><?= Yii::t('message', 'frontend.views.vendor.step_three', ['ru'=>'ШАГ 3']) ?></h4>
                <p><?= Yii::t('message', 'frontend.views.mercury.new_transport_vsd_get_recipient_info', ['ru'=>'Укажите информацию о товарополучателе']) ?></p>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'StockEntryForm',
                /*'enableClientValidation'=>false,
                'enableClientValidation' => false*/]); ?>
            <?php
            $url = \yii\helpers\Url::to(['stock-entry/producers-list']);
            $desc = '';//empty($model->city) ? '' : City::findOne($model->city)->description;

            echo $form->field($model, 'recipient')->widget(Select2::classname(), [
                'initValueText' => $desc, // set the initial display text
                'options' => ['placeholder' => 'Укажите название предприятия для поиска  ...'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Загрузка результатов...'; }"),
                    ],
                    'ajax' => [
                        'url' => $url,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                ],
                'pluginEvents' => [
                    "select2:select" => "function() { 
                     var recipient_guid = $(this).select2(\"data\")[0].id;
                     getHC(recipient_guid, null); 
                      }",
                  ]
            ]);
            ?>

            <?= $form->field($model, 'hc',['enableClientValidation' => false])->hiddenInput()->label(false); ?>
            <?= $form->field($model, 'hc_inn')->textInput(['maxlength' => true, 'id'=>'hc-inn']); ?>
            <?= $form->field($model, 'hc_name')->textInput(['maxlength' => true]); ?>

            <?php $model->isTTN = isset($model->isTTN) ? $model->isTTN : true; ?>
            <?= $form->field($model, 'isTTN')
                ->radioList([
                    true => 'Указать ТТН',
                    false => 'ТТН отсутствует'
                ],['id' => 'isTTN']) ?>

            <div id="TTN-data">
                <?=
                $form->field($model, 'typeTTN')
                    ->dropDownList(\frontend\modules\clientintegr\modules\merc\models\transportVsd\step3Form::$ttn_types,['prompt' => 'не указано']);
                ?>
                <?= $form->field($model, 'seriesTTN')->textInput(['maxlength' => true]); ?>
                <?= $form->field($model, 'numberTTN',['enableClientValidation' => false])->textInput(['maxlength' => true]); ?>
                <?php echo '<label class="control-label"><b>Дата ТТН</b></label>';
                echo $form->field($model, 'dateTTN',['enableClientValidation' => false])->widget(\kartik\widgets\DatePicker::classname(), [
                    'options' => ['placeholder' => 'Дата ТТН'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy'
                    ]
                ])->label(false);
                ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <?php Pjax::end(); ?>
    </div>
</div>
</section>
<?php
$this->registerJs('
/** 
 * Forward port jQuery.live()
 * Wrapper for newer jQuery.on()
 * Uses optimized selector context 
 * Only add if live() not already existing.
*/
if (typeof jQuery.fn.live == "undefined" || !(jQuery.isFunction(jQuery.fn.live))) {
  jQuery.fn.extend({
      live: function (event, callback) {
         if (this.selector) {
              jQuery(document).on(event, this.selector, callback);
          }
      }
  });
}
$(".step-4").click(function(e){
e.preventDefault();
$("#StockEntryForm" ).submit();
});        
');

$customJs = <<< JS
var justSubmitted = false;
var recipient_guid = '';
$("document").ready(function(){
        $("#StockEntryForm").on("change", "#isTTN", function() {
            if(($("input[name='step3Form[isTTN]']:checked").val()) == 1)
                $('#TTN-data').show();
            else {
                $('#TTN-data').hide();
                $('#step3form-seriesttn').val(''); 
                $('#step3form-numberttn').val(''); 
                $('#step3form-datettn').val('');
            }
     }); 
 });

 $(document).on("change keyup paste cut", "#hc-inn", function() {
     if (justSubmitted) {
            clearTimeout(justSubmitted);
        }
        justSubmitted = setTimeout(function() {
            justSubmitted = false;
            var inn = $(this).val();
            var recipient_guid = $('step3form-recipient').select2("data")[0].id;
            getHC(recipient_guid, inn); 
        }, 700);
    });
 
 function getHC(recipient_guid, inn ) {
      $.ajax({
                                     type     :"GET",
                                     cache    : false,
                                     url      : "$urlToGetHC?recipient_guid=" + recipient_guid + "&inn=" + inn,
                                     success  : function(result) {
                                         $('#step3form-hc_name').val(result.name);
                                         $('#step3form-hc').val(result.uuid);
                                    },
                                    error : function ()
                                    {
                                       $('#step3form-hc_name').val(result.name);
                                        $('#step3form-hc').val('Фирма не найдена');
                                    }
                                });
 }
JS;
$this->registerJs($customJs, $this::POS_READY);

$customJs = <<< JS
 function getHC(recipient_guid, inn ) {
      $.ajax({
                                     type     :"GET",
                                     cache    : false,
                                     url      : "$urlToGetHC?recipient_guid=" + recipient_guid + "&inn=" + inn,
                                     success  : function(result) {
                                         $('#step3form-hc_name').val(result.name);
                                         $('#step3form-hc').val(result.uuid);
                                    },
                                    error : function ()
                                    {
                                       $('#step3form-hc_name').val(result.name);
                                        $('#step3form-hc').val('Фирма не найдена');
                                    }
                                });
 }
JS;
$this->registerJs($customJs, $this::POS_HEAD);
?>
