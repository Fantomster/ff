<?php
use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Users;
use dosamigos\switchinput\SwitchBox;
$catalog->isNewRecord ? $this->title = Yii::t('message', 'frontend.views.vendor.new_cat_two', ['ru'=>'Новый каталог']) : $this->title = Yii::t('message', 'frontend.views.vendor.edit_cat', ['ru'=>'Редактирование каталога'])
?>
<section class="content-header">
        <h1 class="margin-right-350">
            <i class="fa fa-list-alt"></i> <?= $catalog->isNewRecord?
            Yii::t('message', 'frontend.views.vendor.create_cat_two', ['ru'=>'Создание нового каталога']) :
            Yii::t('message', 'frontend.views.vendor.edit_cat_two', ['ru'=>'Редактирование каталога']) . '  <small>'.common\models\Catalog::get_value($cat_id)->name.'</small>' ?>
        </h1>
        <?=
        Breadcrumbs::widget([
            'options' => [
                'class' => 'breadcrumb',
            ],
            'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru'=>'Главная']), 'url' => '/'],
            'links' => [
                [
                'label' => Yii::t('message', 'frontend.views.vendor.catalogs_five', ['ru'=>'Каталоги']),
                'url' => ['vendor/catalogs'],
                ],
                $catalog->isNewRecord?
            Yii::t('message', 'frontend.views.vendor.step_one', ['ru'=>'Шаг 1. Создание нового каталога']) :
            Yii::t('message', 'frontend.views.vendor.step_edit', ['ru'=>'Шаг 1. Редактирование каталога']),
            ],
        ])
        ?>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-body">
            <div class="panel-body">
                <ul class="nav fk-tab nav-tabs  pull-left">
                    <?=$catalog->isNewRecord?
                    '<li class="active">'.Html::a(Yii::t('message', 'frontend.views.vendor.name_of_good_three', ['ru'=>'Название']) . '  <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-1'],['class'=>'btn btn-default']).'</li>':
                    '<li class="active">'.Html::a(Yii::t('message', 'frontend.views.vendor.name_of_good_four', ['ru'=>'Название']) . '  <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-1','id'=>$cat_id]).'</li>'
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a(Yii::t('message', 'frontend.views.vendor.add_goods_two', ['ru'=>'Добавить товары'])).'</li>':
                    '<li>'.Html::a(Yii::t('message', 'frontend.views.vendor.goods_three', ['ru'=>'Добавить товары']),['vendor/step-2','id'=>$cat_id]).'</li>'
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a(Yii::t('message', 'frontend.views.vendor.change_prices', ['ru'=>'Изменить цены'])).'</li>':
                    '<li>'.Html::a(Yii::t('message', 'frontend.views.vendor.change_prices_two', ['ru'=>'Изменить цены']),['vendor/step-3-copy','id'=>$cat_id]).'</li>'
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a(Yii::t('message', 'frontend.views.vendor.set_for_rest_two', ['ru'=>'Назначить ресторану'])).'</li>':
                    '<li>'.Html::a(Yii::t('message', 'frontend.views.vendor.set_for_rest_three', ['ru'=>'Назначить ресторану']),['vendor/step-4','id'=>$cat_id]).'</li>'
                    ?>
                </ul>


                <ul class="fk-prev-next pull-right">
                  <?='<li class="fk-next">'.Html::a('<i class="fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.continue', ['ru'=>'Далее']) . ' ',['#'],['class' => 'step-2']).'</li>'?>
                </ul>
        </div>
        <?php Pjax::begin(['id' => 'pjax-container'])?>
        <?php $form = ActiveForm::begin([
            'id' => 'newCatalogForm'
            ]);
        ?>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4><?= Yii::t('message', 'frontend.views.vendor.step_one_two', ['ru'=>'ШАГ 1']) ?></h4>
                <p><?=$catalog->isNewRecord ? Yii::t('message', 'frontend.views.vendor.enter_cat_name', ['ru'=>'Введите название для нового каталога']):Yii::t('message', 'frontend.views.vendor.change_cat_name', ['ru'=>'Изменить название каталога']) ?></p>
            </div>
            <?= $form->field($catalog, 'name')->textInput(['class' => 'form-control input-md'])->label(false) ?>
        </div>
        <?php $form = ActiveForm::end();?>
        <?php Pjax::end(); ?>
    </div>
</div>
</section>
<?php
if($catalog->isNewRecord){$router = Url::to(['vendor/step-1']);}else{$router = Url::to(['vendor/step-1-update', 'id' => $cat_id]);}
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
var urlStap = "'.$router.'";
$.ajax({
    url: urlStap,
    type: "POST",
    dataType: "json",
    data: $("#newCatalogForm" ).serialize(),
    cache: false,
    success: function(response) {
            
            if(response.success){
                var url = "' . Url::toRoute(['vendor/step-2', 'id' => '']) . '"+response.cat_id;
                $(location).attr("href",url);
                }else{
            if(response.type==1){
            bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "' . Yii::t('message', 'frontend.views.vendor.ok_three', ['ru'=>'Окей']) . ' ",
                          className: "btn-success btn-md",
                        },
                    },
                    className: response.alert.class
                });
            }
            console.log(response);    
            }
        },
        failure: function(errMsg) {
        console.log(errMsg);
        }
    });
});        
');
?>
