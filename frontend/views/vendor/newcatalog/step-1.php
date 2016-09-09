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
?>
<?php Pjax::begin(['id' => 'pjax-container'])?>
    <?= Html::a(
        'Сохранить и перейти на шаг 2',
        ['vendor/step-2'],
        ['class' => 'btn btn-success step-2','style' => 'float:right;margin-left:10px;']
    ) ?>
<h2><?= $catalog->isNewRecord? 'Назовите ваш новый каталог' : 'Редактировать каталог' ?></h2>
<?php $form = ActiveForm::begin([
    'id' => 'newCatalogForm'
    ]);
?>
<div class="row">
    <div class="col-lg-12">
    <?= $form->field($catalog, 'name') ?>
    </div>
</div>

<?php $form = ActiveForm::end();?>
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
var urlStap = "'.$route.'";
$.ajax({
    url: urlStap,
    type: "POST",
    dataType: "json",
    data: $("#newCatalogForm" ).serialize(),
    cache: false,
    success: function(response) {
            if(response.success){
                var url = "' . Url::toRoute(['vendor/step-2']) . '"+"&id="+response.cat_id;
                $.pjax({url: url, container: "#pjax-container"});
                }else{
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
<?php Pjax::end(); ?>