<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\Role;
use kartik\checkbox\CheckboxX;
?>
<?php
$form = ActiveForm::begin([
    'id' => 'user-form'
]);
?>
<?=$franchisee->signed ?>

<?= $form->field($franchiseeGeo, 'country')->textInput(['maxlength' => true]) ?>
<?= $form->field($franchiseeGeo, 'city[]')->textInput(['maxlength' => true]) ?>
<input type="text" id="country" value />
<hr>
Исключения:
<?php ActiveForm::end(); ?>
<script type="text/javascript"> 

function stopRKey(evt) { 
var evt = (evt) ? evt : ((event) ? event : null); 
var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
if ((evt.keyCode == 13) && (node.type=="text")) {return false;} 
} 

document.onkeypress = stopRKey; 
</script>
<?php
$gpJsLink= 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
    'libraries' => 'places',
    'key'=>Yii::$app->params['google-api']['key-id'],
    'language'=>Yii::$app->params['google-api']['language'],
    'callback'=>'initAutocomplete'
));
$this->registerJsFile($gpJsLink, ['async'=>true, 'defer'=>true]);
$this->registerJs("
function initAutocomplete() {
autocomplete = new google.maps.places.Autocomplete(
    (document.getElementById('country')),
    {types: ['(regions)']});
autocomplete.addListener('place_changed', fillInAddress);
}
function fillInAddress() {
  var place = autocomplete.getPlace();
  console.log(place);
}
",yii\web\View::POS_END);
?>
