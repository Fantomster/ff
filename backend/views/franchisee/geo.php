<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $model common\models\Franchisee */

$this->title = "Create GEO Franchisee";
$this->params['breadcrumbs'][] = ['label' => 'Franchisees', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $franchisee->id, 'url' => ['view', 'id' => $franchisee->id]];
$this->params['breadcrumbs'][] = 'Регионы франшизы';
?>
<div class="franchisee-create">

    <h1><?= Html::encode($this->title) ?></h1>

<style>#forma button{margin-top:25px}</style>
<div class="row" id="forma">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <h3>Регион Франшизы</h3>  
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>Поиск</label>
                <input type="text" class="form-control autocomplete" id="search_in" name="search_in" placeholder="Поиск">    
            </div>
        </div>
        <?php
        $form = ActiveForm::begin([
            'options' => [
                'id' => 'form-in'
            ],
        ]);
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($franchiseeGeo, 'country')->textInput() ?> 
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($franchiseeGeo, 'administrative_area_level_1')->textInput() ?> 
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($franchiseeGeo, 'locality')->textInput() ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($franchiseeGeo, 'exception')->hiddenInput(['value' => 0])->label(false);?>
                        <button type="submit" class="save btn btn-success">Добавить</button>  
                    </div>
                </div>
            </div>
        </div>
        <?php 
        ActiveForm::end(); 
        ?>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <h3>Исключения</h3>  
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>Поиск</label>
                <input type="text" class="form-control autocomplete" id="search_out" name="search_out" placeholder="Поиск">    
            </div>
        </div>
        <?php
        $form = ActiveForm::begin([
            'options' => [
                'id' => 'form-out'
            ],
        ]);
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($franchiseeGeo, 'country')->textInput() ?> 
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($franchiseeGeo, 'administrative_area_level_1')->textInput() ?> 
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($franchiseeGeo, 'locality')->textInput() ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($franchiseeGeo, 'exception')->hiddenInput(['value' => 1])->label(false);?>
                        <button type="submit" class="save btn btn-success">Добавить</button>  
                    </div>
                        
                </div>
            </div>
        </div>
        <?php 
        ActiveForm::end(); 
        ?>
    </div>
</div>
<hr>

<div class="col-md-12">
    <div class="row">
        <?php Pjax::begin(['id'=>'pjax-container-form']); ?>
        <div class="col-md-6">
            <?php foreach($franchiseeGeoList as $list){ ?>
            <?php if($list->exception == 0){ ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <?=$list->country;?>
                        </div>
                        <div class="col-md-4">
                            <?=$list->administrative_area_level_1;?>
                        </div>
                        <div class="col-md-3">
                            <?=$list->locality;?>
                        </div>
                        <div class="col-md-1">
                            <a href="#" class="delete btn btn-sm btn-danger" style="margin:1px 0;" data-url="<?=Url::toRoute(['franchisee/geo-delete', 'id' => $list->id]);?>">-</a>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <?php } } ?>
        </div>
        <div class="col-md-6">
            <?php foreach($franchiseeGeoList as $list){ ?>
            <?php if($list->exception == 1){ ?> 
            <div class="row">   
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <?=$list->country;?>
                        </div>
                        <div class="col-md-4">
                            <?=$list->administrative_area_level_1;?>
                        </div>
                        <div class="col-md-3">
                            <?=$list->locality;?>
                        </div>
                        <div class="col-md-1">
                            <a href="#" class="delete btn btn-sm btn-danger" style="margin:1px 0;" data-url="<?=Url::toRoute(['franchisee/geo-delete', 'id' => $list->id]);?>">-</a>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <?php } } ?>
        </div>
        <?php Pjax::end(); ?>
    </div>
</div>       



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
    
    var acInputs = document.getElementsByClassName('autocomplete');
    var options = {
      types: ['(regions)'],
      //componentRestrictions: {country: 'ru'}
     };
    for (var i = 0; i < acInputs.length; i++) {
    
        var autocomplete = new google.maps.places.Autocomplete(acInputs[i], options);
        autocomplete.inputId = acInputs[i].id;
        
            google.maps.event.addListener(autocomplete, 'place_changed', function () {

            var address_components=this.getPlace().address_components;
            
            var country='';
            var administrative_area_level_1='';
            var locality='';
            
            for(var j =0 ;j<address_components.length;j++)
            {
                if(address_components[j].types[0]=='country')
                {
                    country = address_components[j].long_name;
                }
                if(address_components[j].types[0]=='administrative_area_level_1')
                {
                    administrative_area_level_1 = address_components[j].long_name;
                }
                if(address_components[j].types[0]=='locality')
                {
                    locality = address_components[j].long_name;
                }  
            }
            if(this.inputId == 'search_in'){
                var form = document.getElementById('form-in');
                form.querySelector('input[id=\"franchiseegeo-country\"]').value = country;
                form.querySelector('input[id=\"franchiseegeo-administrative_area_level_1\"]').value = administrative_area_level_1;
                form.querySelector('input[id=\"franchiseegeo-locality\"]').value = locality;
            }
            if(this.inputId == 'search_out'){
                var form = document.getElementById('form-out');
                form.querySelector('input[id=\"franchiseegeo-country\"]').value = country;
                form.querySelector('input[id=\"franchiseegeo-administrative_area_level_1\"]').value = administrative_area_level_1;
                form.querySelector('input[id=\"franchiseegeo-locality\"]').value = locality;
            }
        });
    }
  }
",yii\web\View::POS_END);
?>
<?php
$this->registerJs('
$(document).ready(
    $("#form-in").on("beforeSubmit", function(event, jqXHR, settings) {
        var form = $(this);
        $(".save").prop("disabled", true);
        if(form.find(".has-error").length) {
            return false;
        }
        console.log(form.serialize())
        $.ajax({
            url: form.attr("action"),
            type: "post",
            data: form.serialize(),
            success: function(data) {
                $(".save").prop("disabled", false);
                $("#form-in input[type=\'text\']").val("")
                $.pjax.reload({container: "#pjax-container-form"});
            }
        });
        return false;
    }),
    $("#form-out").on("beforeSubmit", function(event, jqXHR, settings) {
        var form = $(this);
        $(".save").prop("disabled", true);
        if(form.find(".has-error").length) {
            return false;
        }
        $.ajax({
            url: form.attr("action"),
            type: "post",
            data: form.serialize(),
            success: function(data) {
                $(".save").prop("disabled", false);
                $("#form-out input[type=\'text\']").val("")
                $.pjax.reload({container: "#pjax-container-form"});
            }
        });
        return false;
    }),
);

    $(document).on("click", ".delete", function(e){
    e.preventDefault()
    var url = $(this).attr("data-url");
    var r = confirm("Подтвердите удаление! ");
    if (r == true) {
        $.ajax({
                url: url,
                type: "get",
                success: function(data) {
                    $.pjax.reload({container: "#pjax-container-form"});
                }
            });    
        }
    });
');
?>

</div>
