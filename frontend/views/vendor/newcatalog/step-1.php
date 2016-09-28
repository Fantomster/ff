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
$this->registerCss('.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}');
$catalog->isNewRecord?$this->title = 'Новый каталог':$this->title = 'Редактирование каталога '
?>

  
<div class="panel-body">
      
            <h3 class="font-light">
<?= $catalog->isNewRecord? '<i class="fa fa-list-alt"></i> Создание нового каталога' : '<i class="fa fa-list-alt"></i> Редактирование каталога <strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>' ?>
            </h3>
</div>
<div class="panel-body">
<div class="text-center m-b-sm">
<ul class="nav nav-tabs">
    <?=$catalog->isNewRecord?
    '<li class="active">'.Html::a('Имя каталога',['vendor/step-1']).'</li>':
    '<li class="active">'.Html::a('Имя каталога',['vendor/step-1','id'=>$cat_id]).'</li>' 
    ?>
    <?=$catalog->isNewRecord?
    '<li class="disabled">'.Html::a('Добавить продукты').'</li>':
    '<li>'.Html::a('Добавить продукты',['vendor/step-2','id'=>$cat_id]).'</li>' 
    ?>
    <?=$catalog->isNewRecord?
    '<li class="disabled">'.Html::a('Редактировать').'</li>':
    '<li>'.Html::a('Редактировать',['vendor/step-3-copy','id'=>$cat_id]).'</li>' 
    ?>
    <?=$catalog->isNewRecord?
    '<li class="disabled">'.Html::a('Назначить').'</li>':
    '<li>'.Html::a('Назначить',['vendor/step-4','id'=>$cat_id]).'</li>' 
    ?>
</ul>
</div>
</div>
<?php Pjax::begin(['id' => 'pjax-container'])?>  
<?php $form = ActiveForm::begin([
    'id' => 'newCatalogForm'
    ]);
?>
<div class="panel-body">
    <?= $form->field($catalog, 'name')->textInput(['class' => 'form-control input-md m-b'])->label(false) ?>
    <?= Html::a(
        'Сохранить',
        ['vendor/step-2'],
        ['class' => 'btn btn-lg btn-success pull-right step-2','style' => 'margin-left:10px;']
    ) ?>
</div>


<?php $form = ActiveForm::end();?>
<?php Pjax::end(); ?>
<?php
if($catalog->isNewRecord){$route = 'index.php?r=vendor/step-1';} else {$route = 'index.php?r=vendor/step-1-update&id='.$cat_id;}
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
var urlStap = "'.$route.'";
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
