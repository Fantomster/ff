<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $model common\models\Franchisee */

$this->title = "Регионы доставки";
$this->params['breadcrumbs'][] = ['label' => 'Регионы доставки - поставщик', 'url' => ['index']];
$this->params['breadcrumbs'][] = $supplier->name;
?>
<div class="franchisee-create">

    <h1><?= Html::encode($this->title) ?></h1>

<style>#forma button{margin-top:25px}</style>
<div class="row" id="forma">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <h3>Регионы доставки</h3>  
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
                        <?= $form->field($deliveryRegions, 'country')->textInput() ?> 
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($deliveryRegions, 'administrative_area_level_1')->textInput() ?> 
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($deliveryRegions, 'locality')->textInput() ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($deliveryRegions, 'exception')->hiddenInput(['value' => 0])->label(false);?>
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
                        <?= $form->field($deliveryRegions, 'country')->textInput() ?> 
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($deliveryRegions, 'administrative_area_level_1')->textInput() ?> 
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($deliveryRegions, 'locality')->textInput() ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($deliveryRegions, 'exception')->hiddenInput(['value' => 1])->label(false);?>
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
            <?php foreach($regionsList as $list){ ?>
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
                            <a href="#" class="delete btn btn-sm btn-danger" style="margin:1px 0;" data-url="<?=Url::toRoute(['delivery-regions/remove', 'id' => $list->id]);?>">-</a>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <?php } } ?>
        </div>
        <div class="col-md-6">
            <?php foreach($regionsList as $list){ ?>
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
                            <a href="#" class="delete btn btn-sm btn-danger" style="margin:1px 0;" data-url="<?=Url::toRoute(['delivery-regions/remove', 'id' => $list->id]);?>">-</a>
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
\backend\assets\GoogleMapsRegionsAsset::register($this);
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
    var r = confirm("' . Yii::t('app', 'Подтвердите удаление!') . ' ");
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


