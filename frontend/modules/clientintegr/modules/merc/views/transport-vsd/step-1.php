<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\widgets\Pjax;
use common\models\Users;
use unclead\multipleinput\TabularInput;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
$this->title = Yii::t('message', 'frontend.views.mercury.new_transport_vsd', ['ru'=>'Новый транспортный ВСД ']);
$style= <<< CSS

  .js-input-remove {
    display: none;
  }
  .js-input-plus {
    display: none;
  }
CSS;
 $this->registerCss($style);
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
            Yii::t('message', 'frontend.views.mercury.new_transport_vsd_step_one', ['ru'=>'Шаг 1. Создание нового транспортного ВСД'])
        ],
    ])
?>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-body">
            <div class="panel-body">
                <ul class="nav fk-tab nav-tabs  pull-left">
                    <?= '<li class="active">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_select_product', ['ru'=>' Выбор продукции']) . '  <i class="fa fa-fw fa-hand-o-right"></i>',['step-1'],['class'=>'btn btn-default']).'</li>';?>
                    <?= '<li class="">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_vet_info', ['ru'=>'Ветеринарная Экспертиза'])).'</li>';?>
                    <?= '<li class="">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_recipient_info', ['ru'=>' Информация о товарополучателе'])).'</li>'?>
                    <?= '<li class="">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_transport_info', ['ru'=>'Информация о транспорте'])).'</li>'?>
                </ul>


                <ul class="fk-prev-next pull-right">
                  <?='<li class="fk-next">'.Html::a('<i class="fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.continue', ['ru'=>'Далее']) . ' ',['#'],['class' => 'step-2']).'</li>'?>
                </ul>
        </div>
        <?php Pjax::begin(['id' => 'pjax-container'])?>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4><?= Yii::t('message', 'frontend.views.vendor.step_one_two', ['ru'=>'ШАГ 1']) ?></h4>
                <p><?=Yii::t('message', 'frontend.views.mercury.new_transport_vsd_select_product', ['ru'=>' Выбор продукции']) ?></p>
            </div>
            <?php
            $form = ActiveForm::begin([
                'enableAjaxValidation' => false,
                'enableClientValidation' => false,
                'validateOnChange' => false,
                'validateOnSubmit' => true,
                'validateOnBlur' => false,
                'options' => ['style' => "width: 100%;", 'id' => 'product_list_form']]);

            echo TabularInput::widget([
                'models' => $list,
                'attributeOptions' => [
                    'enableAjaxValidation' => false,
                    'enableClientValidation' => false,
                    'validateOnChange' => false,
                    'validateOnSubmit' => true,
                    'validateOnBlur' => false,
                ],
                'columns' => [
                    [
                        'name'  => 'id',
                        'title' => 'ID',
                        'type'  => \unclead\multipleinput\MultipleInputColumn::TYPE_HIDDEN_INPUT,
                    ],
                    [
                        'name'  => 'product_name',
                        'title' =>  Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']),
                        'enableError' => true,
                    ],
                    [
                        'name'  => 'select_amount',
                        'title' =>  Yii::t('message', 'frontend.client.integration.volume', ['ru' => 'Объём']),
                        'enableError' => true,
                        'type' => \kartik\widgets\TouchSpin::className(),
                        'options' => function ($data) { return [
                            'pluginOptions' => [
                                'initval' => isset($data->select_amount) ? $data->select_amount : $data->amount,
                                'min' => 0.0001,
                                'max' => $data->amount,
                                'step' =>  0.01,
                                'decimals' => 3,
                                //'decimals' => (empty($data["units"]) || (fmod($data["units"], 1) > 0)) ? 3 : 0,
                                //'forcestepdivisibility' => (isset($data['units']) && $data['units'] && (floor($data['units']) == $data['units'])) ? 'floor' : 'none',
                                'buttonup_class' => 'btn btn-default',
                                'buttondown_class' => 'btn btn-default',
                                'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                            ],
                        ];}
                    ],
                    [
                        'name'  => 'amount',
                        'title' =>  Yii::t('message', 'frontend.client.integration.max_volume', ['ru' => 'Макс. Объём']),
                        'type'  => 'static',
                        'value' => function ($data) {
                            return round($data->amount);
                        }
                    ],
                    [
                        'name' => 'unit',
                        'title' => 'Ед. измерения',
                        'type'  => 'static',
                    ],
                ]
            ]); ?>
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
$(".step-2").click(function(e){
e.preventDefault();
$("#product_list_form" ).submit();
});        
');
?>
