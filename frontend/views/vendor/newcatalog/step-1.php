<?php
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
$catalog->isNewRecord?$this->title = 'Новый каталог':$this->title = 'Редактирование каталога '
?>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $catalog->isNewRecord? 'Создание нового каталога' : 'Редактирование каталога <strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>' ?>
        </h3>
        <span class="pull-right"><?=Html::a('<i class="fa fa-fw fa-chevron-left"></i>  Вернуться к списку каталогов',['vendor/catalogs'])?></span>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="panel-body">
            <div class="text-center">
                <ul class="nav fk-tab nav-tabs pull-left">
                    <?=$catalog->isNewRecord?
                    '<li class="active">'.Html::a('Название <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-1'],['class'=>'btn btn-default']).'</li>':
                    '<li class="active">'.Html::a('Название <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-1','id'=>$cat_id]).'</li>' 
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a('Добавить товары').'</li>':
                    '<li>'.Html::a('Добавить товары',['vendor/step-2','id'=>$cat_id]).'</li>' 
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a('Изменить цены').'</li>':
                    '<li>'.Html::a('Изменить цены',['vendor/step-3-copy','id'=>$cat_id]).'</li>' 
                    ?>
                    <?=$catalog->isNewRecord?
                    '<li class="disabled">'.Html::a('Назначить').'</li>':
                    '<li>'.Html::a('Назначить',['vendor/step-4','id'=>$cat_id]).'</li>' 
                    ?>
                </ul>
                <ul class="fk-prev-next pull-right">
                  <?='<li class="fk-prev">'.Html::a('Назад',['vendor/step-1']).'</li>'?>
                  <?='<li class="fk-next">'.Html::a('Сохранить и продолжить',['#'],['class' => 'step-2']).'</li>'?>
                </ul>
                
            </div>
        </div>
        <?php Pjax::begin(['id' => 'pjax-container'])?>  
        <?php $form = ActiveForm::begin([
            'id' => 'newCatalogForm'
            ]);
        ?>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4>ШАГ 1</h4>
                <p><?=$catalog->isNewRecord ? 'Введите название для нового каталога':'Изменить название каталога' ?></p>
            </div>
            <?= $form->field($catalog, 'name')->textInput(['class' => 'form-control input-md'])->label(false) ?>
        </div>
        <?php $form = ActiveForm::end();?>
        <?php Pjax::end(); ?>
    </div>
</div>    
<?php
if($catalog->isNewRecord){$router = 'index.php?r=vendor/step-1';}else{$router = 'index.php?r=vendor/step-1-update&id='.$cat_id;}
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
                //bootbox.alert("<h3>Сохранено!</h3>");
                var url = "' . Url::toRoute(['vendor/step-2']) . '"+"&id="+response.cat_id;
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
                          label: "Окей",
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
