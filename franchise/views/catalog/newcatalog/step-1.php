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
use nirvana\showloading\ShowLoadingAsset;
ShowLoadingAsset::register($this);
$catalog->isNewRecord ? $this->title = Yii::t('app', 'franchise.views.catalog.newcatalog.new_cat', ['ru'=>'Новый каталог']) : $this->title = Yii::t('app', 'franchise.views.catalog.newcatalog.edit_cat', ['ru'=>'Редактирование каталога'])
?>
<section class="content-header">
        <h1>
            <i class="fa fa-list-alt"></i> <?= $catalog->isNewRecord? 
            Yii::t('app', 'franchise.views.catalog.newcatalog.creating_new_cat', ['ru'=>'Создание нового каталога']) :
            Yii::t('app', 'franchise.views.catalog.newcatalog.edit_cat_two', ['ru'=>'Редактирование каталога <small>']).common\models\Catalog::get_value($cat_id)->name.'</small>' ?>
        </h1>
        <?=
        Breadcrumbs::widget([
            'options' => [
                'class' => 'breadcrumb',
            ],
            'links' => [
                [
                'label' => Yii::t('app', 'franchise.views.catalog.newcatalog.catalogs', ['ru'=>'Каталоги']),
                'url' => ['catalog/index', 'vendor_id'=>$vendor_id],
                ],
                $catalog->isNewRecord? 
            Yii::t('app', 'franchise.views.catalog.newcatalog.step_one', ['ru'=>'Шаг 1. Создание нового каталога']) :
            Yii::t('app', 'franchise.views.catalog.newcatalog.step_one_two', ['ru'=>'Шаг 1. Редактирование каталога']),
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
                    '<li class="active">'.Html::a(' ' . Yii::t('app', 'franchise.views.catalog.newcatalog.title', ['ru'=>'Название']) . '  <i class="fa fa-fw fa-hand-o-right"></i>',['catalog/step-1', 'vendor_id'=>$vendor_id],['class'=>'btn btn-default']).'</li>':
                    '<li class="active">'.Html::a(' ' . Yii::t('app', 'franchise.views.catalog.newcatalog.title_two', ['ru'=>'Название']) . '  <i class="fa fa-fw fa-hand-o-right"></i>',['catalog/step-1-update', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.add_goods', ['ru'=>'Добавить товары'])).'</li>':
                    '<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.add_goods_two', ['ru'=>'Добавить товары']),['catalog/step-2', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.change_prices', ['ru'=>'Изменить цены'])).'</li>':
                    '<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.change_prices_two', ['ru'=>'Изменить цены']),['catalog/step-3-copy', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.settle_to_rest', ['ru'=>'Назначить ресторану'])).'</li>':
                    '<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.settle_to_rest_two', ['ru'=>'Назначить ресторану']),['catalog/step-4', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'
                    ?>
                </ul>
        
            
                <ul class="fk-prev-next pull-right">
                  <?='<li class="fk-next">'.Html::a('<i class="fa fa-save"></i> ' . Yii::t('app', 'franchise.views.catalog.newcatalog.farther', ['ru'=>'Далее']) . ' ',['#'],['class' => 'step-2']).'</li>'?>
                </ul>
        </div>
        <?php Pjax::begin(['id' => 'pjax-container'])?>  
        <?php $form = ActiveForm::begin([
            'id' => 'newCatalogForm'
            ]);
        ?>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4><?= Yii::t('app', 'franchise.views.catalog.newcatalog.step_one_three', ['ru'=>'ШАГ 1']) ?></h4>
                <p><?=$catalog->isNewRecord ? Yii::t('app', 'franchise.views.catalog.newcatalog.enter_new_cat_name', ['ru'=>'Введите название для нового каталога']):Yii::t('app', 'franchise.views.catalog.newcatalog.change_cat_name', ['ru'=>'Изменить название каталога']) ?></p>
            </div>
            <?= $form->field($catalog, 'name')->textInput(['class' => 'form-control input-md'])->label(false) ?>
        </div>
        <?php $form = ActiveForm::end();?>
        <?php Pjax::end(); ?>
    </div>
</div> 
</section>
<?php
if($catalog->isNewRecord){$router = Url::to(['catalog/step-1', 'vendor_id' => $vendor_id]);}else{$router = Url::to(['catalog/step-1-update', 'vendor_id' => $vendor_id, 'id'=>$cat_id]);}
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
//$("#loader-show").showLoading();
var urlStap = "'.$router.'";
$.ajax({
    url: urlStap,
    type: "POST",
    dataType: "json",
    data: $("#newCatalogForm" ).serialize(),
    cache: false,
    success: function(response) {
            
            if(response.success){
                //bootbox.alert("<h3>' . Yii::t('app', 'franchise.views.catalog.newcatalog.saved', ['ru'=>'Сохранено!']) . '</h3>");
                var url = "' . Url::toRoute(['catalog/step-2', 'vendor_id'=>$vendor_id, 'id' => '']) . '"+response.cat_id;
                $(location).attr("href",url);
                //$.pjax({url: url, container: "#pjax-container"});
                //$("#loader-show").hideLoading();
                }else{
            if(response.type==1){
            bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "' . Yii::t('app', 'franchise.views.catalog.newcatalog.ok', ['ru'=>'Ok']) . '",
                          className: "btn-success btn-md",
                        },
                    },
                    className: response.alert.class
                });
            //$("#loader-show").hideLoading();
            }
            console.log(response);    
            }
        },
        failure: function(errMsg) {
        //$("#loader").hideLoading();
        console.log(errMsg);
        }
    });
});        
');
?>
