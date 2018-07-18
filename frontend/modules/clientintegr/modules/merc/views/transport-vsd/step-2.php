<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\widgets\Pjax;
use common\models\Users;
use unclead\multipleinput\TabularInput;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
$this->title = Yii::t('message', 'frontend.views.mercury.new_transport_vsd', ['ru'=>'Новый транспортный ВСД ']);
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
            Yii::t('message', 'frontend.views.mercury.new_transport_vsd_step_two', ['ru'=>'Шаг 2. Создание нового транспортного ВСД'])
        ],
    ])
?>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-body">
            <div class="panel-body">
                <ul class="nav fk-tab nav-tabs  pull-left">
                    <?= '<li class="disabled">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_select_product', ['ru'=>' Выбор продукции']) . '  <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-1'],['class'=>'btn btn-default']).'</li>';?>
                    <?= '<li class="active">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_recipient_info', ['ru'=>' Информация о товарополучателе'])).'</li>'?>
                    <?= '<li class="disabled">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_transport_info', ['ru'=>'Информация о транспорте'])).'</li>'?>
                </ul>
                <ul class="fk-prev-next pull-right">
                  <?= '<li class="fk-prev">' . Html::a(Yii::t('message', 'frontend.views.vendor.back', ['ru'=>'Назад']), ['step-1']) . '</li>' ?>
                  <?='<li class="fk-next">'.Html::a('<i class="fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.continue', ['ru'=>'Далее']) . ' ',['#'],['class' => 'step-2']).'</li>'?>
                </ul>
        </div>
        <?php Pjax::begin(['id' => 'pjax-container'])?>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4><?= Yii::t('message', 'frontend.views.vendor.step_two', ['ru'=>'ШАГ 2']) ?></h4>
                <p><?= Yii::t('message', 'frontend.views.mercury.new_transport_vsd_get_recipient_info', ['ru'=>'Укажите информацию о товарополучателе']) ?></p>
            </div>

        </div>
        <?php Pjax::end(); ?>
    </div>
</div>
</section>
<?php
$router = Url::to(['step-1']);
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
//var urlStep = "'.$router.'";
$("#product_list_form" ).submit();
/*$.ajax({
    url: urlStap,
    type: "POST",
    dataType: "json",
    data: $("#product_list_form" ).serialize(),
    cache: false,
    success: function(response) {
            
            if(response.success){
                var url = "' . Url::toRoute(['step-2']) . '";
                $(location).attr("href",url);
                }else{
                alert(1);
           $("#product_list_form" ).replaceWith(result);
            console.log(response);    
            }
        },
        failure: function(errMsg) {
        console.log(errMsg);
        }
    });*/
});        
');
?>
