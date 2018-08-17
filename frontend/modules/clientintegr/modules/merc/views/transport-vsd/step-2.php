<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use common\models\Users;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use kartik\widgets\Select2;

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
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru' => 'Интеграция']),
                'url' => ['/clientintegr/default'],
            ],
            Yii::t('message', 'frontend.views.mercury.new_transport_vsd_step_two', ['ru' => 'Шаг 2. Создание нового транспортного ВСД'])
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body">
            <div class="panel-body">
                <ul class="nav fk-tab nav-tabs  pull-left">
                    <?= '<li class="">' . Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_select_product', ['ru' => ' Выбор продукции'])) . '</li>'; ?>
                    <?= '<li class="active">' . Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_vet_info', ['ru' => 'Ветеринарная Экспертиза']) . ' <i class="fa fa-fw fa-hand-o-right"></i>', ['step-2'], ['class' => 'btn btn-default']) . '</li>'; ?>
                    <?= '<li class="">' . Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_recipient_info', ['ru' => ' Информация о товарополучателе'])) . '</li>' ?>
                    <?= '<li class="">' . Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_transport_info', ['ru' => 'Информация о транспорте'])) . '</li>' ?>
                </ul>
                <ul class="fk-prev-next pull-right">
                    <?= '<li class="fk-prev">' . Html::a(Yii::t('message', 'frontend.views.vendor.back', ['ru' => 'Назад']), ['step-1']) . '</li>' ?>
                    <?= '<li class="fk-next">' . Html::a('<i class="fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.continue', ['ru' => 'Далее']) . ' ', ['#'], ['class' => 'step-3']) . '</li>' ?>
                </ul>
            </div>
            <?php Pjax::begin(['id' => 'pjax-container']) ?>
            <div class="panel-body">
                <div class="callout callout-fk-info">
                    <h4><?= Yii::t('message', 'frontend.views.vendor.step_two', ['ru' => 'ШАГ 2']) ?></h4>
                    <p><?= Yii::t('message', 'frontend.views.mercury.new_transport_vsd_get_vet_info', ['ru' => 'Укажите информацию о ветеринарной экспертизе']) ?></p>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'StockEntryForm']); ?>
                <?=
                $form->field($model, 'purpose')->widget(Select2::classname(), [
                    'data' => $model->getPurposeList(),
                    'options' => ['placeholder' => 'Укажите цель'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);
                ?>

                <?php $model->cargoExpertized = isset($model->cargoExpertized) ? $model->cargoExpertized : 'VSEFULL'; ?>

                <?=
                        $form->field($model, 'cargoExpertized')
                        ->dropDownList($model->getExpertizeList(), ['prompt' => 'не указано']);
                ?>

                <?= $form->field($model, 'locationProsperity')->textInput(['maxlength' => true]); ?>
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
$(".step-3").click(function(e){
e.preventDefault();
$("#StockEntryForm" ).submit();
});        
');
?>
