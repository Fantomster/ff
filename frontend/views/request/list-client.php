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
$this->title = 'Заявки';
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
    #create .modal.fade .modal-dialog {
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

    #create .modal.fade.in .modal-dialog {
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
        <small>Разместите заявку и ее увидят все поставщики системы MixCart</small>
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
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="box box-info">
                        <div class="box-body no-padding" style="padding-bottom:15px !important;padding-top:15px !important;">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <?=
                                    Html::input('text', 'search', \Yii::$app->request->get('search')? : '', [
                                        'class' => 'form-control',
                                        'placeholder' => 'Поиск',
                                        'id' => 'search'
                                    ]);
                                    ?>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                <?php
                                Modal::begin([
                                    'id' => 'create',
                                    'toggleButton' => [
                                        'id' => 'create-button',
                                        'label' => '<i class="fa fa-paper-plane"></i> Разместить заявку',
                                        'class' => 'btn btn-sm btn-fk-success',
                                        'disabled' => 'disabled',
                                        'data-loading-text' => '<i class="fa fa-circle-o-notch fa-spin"></i> Подождите...',
                                        'style' => 'width:100%'],
                                    'options' => ['class' => 'modal-fs fade modal', 'tabindex' => '-1']
                                ]);
                                ?>
<?php
echo $this->render("create", compact('request', 'organization', 'profile'));
Modal::end();
?>   
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="box box-info">
                        <div class="box-body">
                            <div class="col-md-12 no-padding">
                                <?php
                                Pjax::begin([
                                    'id' => 'list',
                                    'timeout' => 10000,
                                    'enablePushState' => false,
                                ]);
                                ?> 

                                <?=
                                ListView::widget([
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
                                            'options' => [
                                                'class' => 'col-lg-12 list-wrapper inline no-padding'
                                            ],
                                            'layout' => "\n{items}\n<div class='pull-left'>{pager}</div><div class='pull-right summary-pages'>{summary}</div>",
                                            'summary' => 'Показано {count} из {totalCount}',
                                            'emptyText' => 'Список пуст',
                                        ])
                                        ?>
        <?php Pjax::end(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 hidden-xs hidden-sm">
        <?= ''//Html::a('<img src="/images/banners/240х400_Restorating.ru.gif" >', "https://www.restorating.ru/promo-msk/?utm_source=MixCart&utm_medium=banner&utm_campaign=test_pack", ['class' => 'img-responsive', 'style' => 'margin-bottom:15px']) ?>
        <?= ''//Html::a('<img src="/images/banners/240х400_pmkmebel.jpg" >', "http://pmkmebel.ru/", ['class' => 'img-responsive', 'style' => 'margin-bottom:15px']) ?>
                </div>
        </section>
        <?php
//$this->registerJs('$("#create-button").button("loading");',yii\web\View::POS_LOAD);
        $this->registerJs('$("#create-button").removeAttr("disabled");', yii\web\View::POS_READY);
        $this->registerJsFile(Yii::$app->request->BaseUrl . '/modules/jquery-ui.min.js', ['depends' => [yii\web\JqueryAsset::className()]]);
        $this->registerJs('
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
    var url = "' . Url::to(["request/view", 'id' => '']) . '" + id;
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
            if(response["organization-address"] == false){
                $(".field-organization-address input").css("border-bottom","2px solid red");
                setTimeout(function() {$(".field-organization-address input").css("border-bottom","2px solid #ccc");}, 1500)
            }
            if((typeof(response["request-category"]) != "undefined" && 
              response["request-category"] !== null) || 
               (typeof(response["request-product"]) != "undefined" && 
              response["request-product"] !== null) || response["organization-address"] == false){
              form.yiiActiveForm("submitForm")
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
           }else{
           console.log(response)
           }
       }
       cur.removeAttr("disabled"); 
    }
    });
});

function cancel(){
$("fieldset").addClass(animationName).one(animationend,function() {
    $(this).removeClass(animationName);
});
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
$("#create").on("shown.bs.modal", function () {
    if (typeof initMap == "function") {
        initMap();
    }
});
', yii\web\View::POS_END);

        