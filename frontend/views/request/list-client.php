<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii2assets\fullscreenmodal\FullscreenModal;
use delocker\animate\AnimateAssetBundle;
AnimateAssetBundle::register($this);
yii2assets\fullscreenmodal\FullscreenModalAsset::register($this);
$request = new \common\models\Request();
?>
<style>
    .req-items{
    background: #fff;
    border-bottom: 1px solid #f4f4f4;
    position: relative;
    padding: 10px;
    margin-top:10px;
    }
    .req-items:hover, .req-name:hover{
    border-bottom:1px solid #84bf76;
    cursor:pointer
    }
.req-name{color:#84bf76;font-size:22px;margin-top:20px}
.req-fire{margin-left:10px;color:#d9534f;font-size:18px;}
.req-nal-besnal{margin-left:10px}
.req-category{}
.req-discription{font-size:18px;color:#757575}
.req-created{font-size:12px;color:#757575}
.req-visits{font-size:12px;color:#757575}
.req-comments{font-size:12px;color:#757575}
.modal.fade .modal-dialog {
    -webkit-transform: scale(0.1);
    -moz-transform: scale(0.1);
    -ms-transform: scale(0.1);
    transform: scale(0.1);
    top: 300px;
    opacity: 0;
    -webkit-transition: all 0.3s;
    -moz-transition: all 0.3s;
    transition: all 0.3s;
}

.modal.fade.in .modal-dialog {
    -webkit-transform: scale(1);
    -moz-transform: scale(1);
    -ms-transform: scale(1);
    transform: scale(1);
    -webkit-transform: translate3d(0, -300px, 0);
    transform: translate3d(0, -300px, 0);
    opacity: 1;
}
.req-name{font-size:16px;font-weight:bold;letter-spacing:0.02em;}
.req-fire{font-size:14px;font-weight:normal}
.req-cat{font-size:12px;font-weight:normal;color:#828384}
.req-cat-name{font-size:12px;font-weight:bold;color:#828384}
.req-nal-besnal{font-size:12px;font-weight:bold;color:#828384}
.summary-pages{font-size:12px;font-weight:normal;color:#828384;margin-top:27px}
.req-discription{font-size:14px;font-weight:normal;color:#95989a}
.req-created{font-size:12px;font-weight:normal;color:#828384;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-paper-plane"></i> Список заявок
        <small>Разместите заявку и ее увидят все поставщики системы f-keeper</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Список заявок'
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="col-md-6 no-padding">
                <div class="input-group">
                    <span class="input-group-addon">
                      <i class="fa fa-search"></i>
                    </span>
                <?=Html::input('text', 'search', \Yii::$app->request->get('search')?:'',
                        [
                            'class' => 'form-control',
                            'placeholder'=>'Поиск',
                            'id'=>'search'
                        ]);?>
                </div>
            </div>
            <div class="col-md-6 no-padding">
            <?php Modal::begin([
            'id' => 'create',
            'toggleButton' => ['label' => '<i class="fa fa-paper-plane"></i> Разместить заявку','class'=>'btn btn-sm btn-fk-success pull-right'],
            'options'=>['class'=>'modal-fs fade modal','tabindex'=>'-1'],
            /*'clientOptions' => false,
                'toggleButton' => [
                    'label' => '<i class="fa fa-paper-plane"></i> Разместить заявку',
                    'tag' => 'a',
                    'data-target' => '#create',
                    'class'=>'btn btn-sm btn-fk-success pull-right',
                ],*/
         ]);
            ?>
            </div>
            <?php
            echo $this->render("create", compact('request','organization','profile'));
            Modal::end();?>     
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Мои заявки</h3> 
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="col-md-12 no-padding">
              
              <?php 
              Pjax::begin([
                  'id' => 'list', 
                  'timeout' => 10000, 
                  'enablePushState' => false,
                  ]);
              ?> 
              
              <?=ListView::widget([
                    'dataProvider' => $dataListRequest,
                    'itemView' => function ($model, $key, $index, $widget) {
                        return $this->render('list/_listView', ['model' => $model]);
                        },
                    'pager' => [
                        'maxButtonCount' => 5,
                            'options' => [
                            'class' => 'pagination col-md-12  no-padding'
                        ],
                    ],
                    'options'=>[
                      'class'=>'col-lg-12 list-wrapper inline no-padding'
                    ],
                    'layout' => "\n{items}\n<div class='pull-left'>{pager}</div><div class='pull-right summary-pages'>{summary}</div>",
                    'summary' => 'Показано {count} из {totalCount}',
                    'emptyText' => 'Список пуст',
                ])?>
              <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</section>
<?php

$gpJsLink= 'http://maps.googleapis.com/maps/api/js?' . http_build_query(array(
    'libraries' => 'places',
    'key'=>'AIzaSyCBVFLS9LMiR5CYyONNCi7A5vh2p7l9r8M',
    'callback'=>'initAutocomplete'
));
$this->registerJsFile($gpJsLink, ['depends' => [yii\web\JqueryAsset::className()],'async'=>true, 'defer'=>true]);

$this->registerJsFile(Yii::$app->request->BaseUrl . '/modules/jquery-ui.min.js', ['depends' => [yii\web\JqueryAsset::className()]]);
$this->registerJs('
var placeSearch, autocomplete;
var componentForm = {
    country: "long_name",
    locality: "long_name",
    route: "long_name"
};
function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(
    (document.getElementById("autocomplete")),
    {types: ["geocode"]});
    autocomplete.addListener("place_changed", fillInAddress);
}

function fillInAddress() {
  var place = autocomplete.getPlace();
  for (var component in componentForm) {
    document.getElementById(component).value = "";
    document.getElementById(component).disabled = false;
  }
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm[addressType]) {
        var val = place.address_components[i][componentForm[addressType]];
        document.getElementById(addressType).value = val;
    }
  }
}
function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };
      var circle = new google.maps.Circle({
        center: geolocation,
        radius: position.coords.accuracy
      });
      autocomplete.setBounds(circle.getBounds());
    });
  }
}
$("#create").removeAttr("tabindex");
$("#create .modal-content").css("overflow-y","auto")
var timer;
$("#search").on("keyup put paste change", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: "GET",
        push: true,
        url: "' . Url::to(["request/list"]) . '",
        container: "#list",
        data: { search: $("#search").val()}
      });
   }, 700);
});
 
$("body").on("hidden.bs.modal", "#create", function() {
    $.pjax.reload({container:"#pjax-create", async:false});
    
});
$(document).on("click", ".req-items", function() {
    var id = $(this).attr("data-id");
    var url = "' . Url::to(["request/view"]) . '&id=" + id;
    window.location.href = url;
})  

var current_fs, next_fs, previous_fs;
var left, opacity, scale;
var animating;
var errorStep = true;
var animationName = "animated shake";
var animationend = "webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend";
$(document).on("click",".next",function(e){
    var form = $("#msform");
    var data = form.data("yiiActiveForm");
    var cur = $(this);
        cur.prop("disabled",true);
    var step = $(this).attr("data-step");
    
    $.ajax({
    url: "' . Url::to(["request/save-request"]) . '",
    type: "POST",
    dataType: "json",
    data: form.serialize() + "&step=" + step,
    cache: false,
    success: function (response) {
       if(step == 1){
            if((typeof(response["request-category"]) != "undefined" && 
              response["request-category"] !== null) || 
               (typeof(response["request-product"]) != "undefined" && 
              response["request-product"] !== null)){
              form.yiiActiveForm("submitForm")
              
              $("fieldset").addClass(animationName).one(animationend,function() {
                $(this).removeClass(animationName);
              });
              cancel();
            }else{
                form.yiiActiveForm("resetForm");
                next(cur); 
            }
       }
       if(step == 2){ 
            if(typeof(response["request-amount"]) != "undefined" && 
              response["request-amount"] !== null){
              form.yiiActiveForm("submitForm")
              $("fieldset").addClass(animationName).one(animationend,function() {
                $(this).removeClass(animationName);
              });
              cancel();  
            }else{
              form.yiiActiveForm("resetForm");
              next(cur);  
            }
       } 
       if(step == 3){ 
       $.pjax.reload({container:"#list", async:false});
           if(response["saved"]){ 
            $("#create").modal("hide"); 
           }
       }
       cur.removeAttr("disabled"); 
    }
    });
});

function cancel(){
return false;    
}        
function next(e) {
if(animating) return false;
    animating = true;
    current_fs = e.parent();
    next_fs = e.parent().next();

    $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

    next_fs.show(); 
    current_fs.animate({opacity: 0}, {
        step: function(now, mx) {
                scale = 1 - (1 - now) * 0.2;
                left = (now * 50)+"%";
                opacity = 1 - now;
                current_fs.css({"transform": "scale("+scale+")"});
                next_fs.css({"right": left, "opacity": opacity});
        }, 
        duration: 800, 
        complete: function(){
                current_fs.hide();
                animating = false;
        }, 
        easing: "easeInOutBack"
    });    
}
      
function previous(e) {
if(animating) return false;
animating = true;

current_fs = e.parent();
previous_fs = e.parent().prev();

$("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

previous_fs.show(); 
current_fs.animate({opacity: 0}, {
        step: function(now, mx) {
                scale = 0.8 + (1 - now) * 0.2;
                left = ((1-now) * 50)+"%";
                opacity = 1 - now;
                current_fs.css({"right": left});
                previous_fs.css({"transform": "scale("+scale+")", "opacity": opacity});
        }, 
        duration: 800, 
        complete: function(){
                current_fs.hide();
                animating = false;
        }, 
        easing: "easeInOutBack"
});    
}    
$(document).on("click",".previous",function(){
    previous($(this));
})
',yii\web\View::POS_END);
?>
