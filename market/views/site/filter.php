<?php
use yii\bootstrap\ActiveForm;
//use kartik\widgets\Typeahead;
use yii\helpers\Html;
use yii\web\View;
?>

<?php
$this->title = 'F-MARKET главная';
?>

<div id="overlow-search-result"></div>
<section id="search_block">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="input-group">
                  <span class="input-group-addon" id="basic-addon1" style="background-color: #fff;border: none;">
                        <i class="fa fa-search" style="color:rgba(63,62,62,0.3);font-size: 18px"></i>
                  </span>
                  <input  id="search" type="text" class="form-control search-block" placeholder="Поиск товаров и поставщиков" aria-describedby="basic-addon1">
                </div> 
            </div>
        </div>
    </div>
</section>
<section id="search-result">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 search-result-block">
             <div id="result"><?=$this->renderAjax('main/_search_form');?></div>
            </div>
        </div>
    </div>
</section>
<section  id="features1-u">
    <div class="container">
      <div class="row">
            <div class="col-md-12">
                  <div class="row">
                    <div class="col-md-4 right-padding" style="margin-bottom: 30px;" >
                          <div class="row">
                                <div class="col-md-12">
                                          <h3>Каталог <span class="badge pull-right">122342 товаров</span></h3>  
                                </div>
                          </div>
                          <div class="row">
                                <div class="col-md-12">
                                  <div class="category">
                                    <ul class="list-unstyled">
                                      <li>
                                            <div class="dropdown">
                                                  <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Мясо <span class="badge">1223</span>
                                                        <span class="caret pull-right"></span>
                                                  </button>
                                                  <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                                    <div class="col-sm-12 no-padding">
                                                          <div class="sub-cat">
                                                                <ul class="list-unstyled">
                                                                  <li><a href="" title="">Курица <span class="badge">122342</span></a></li>
                                                                    <li><a href="" title="">Свинина</a></li>
                                                                        <li><a href="" title="">Баранина</a></li>
                                                                        <li><a href="" title="">Свинина</a></li>
                                                                        <li><a href="" title="">Баранина</a></li>
                                                                </ul>
                                                          </div>
                                                        </div>
                                                  </div>
                                                </div>
                                          </li>
                                          <li><a href="" title="">Птица <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Рыба <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Морепродукты <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Молочные продукты <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Безалкогольные напитки <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Птица <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Рыба <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Морепродукты <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Молочные продукты <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Безалкогольные напитки <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Птица <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Рыба <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Морепродукты <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Молочные продукты <span class="badge">122342</span></a></li>
                                          <li><a href="" title="">Безалкогольные напитки <span class="badge">122342</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                          </div>
                    </div>
                  </div> 
            </div>
      </div>
    </div>
</section>

<?php
$customJs = <<< JS
$('#search').on("keyup", function () {
    var searchText = $(this).val();
    console.log(searchText)    
    $.ajax({
        url: "index.php?r=site/view",
        type: "POST",
        data: {'searchText' : searchText},
        cache: false,
        success: function(response) {
            $('#result').html(response);        
        }
    });
});
$(document).ready(function(){
        $(document).on('focusin','#search', function(e){
                var obj = $('#search-result');
                var objs = $('#search_block');
                var ovr = $('#overlow-search-result');
                objs.addClass('shadow-bottom-light');
                $('#features1-u').addClass('blur');
                obj.animate({ 
                        zIndex: 100,
                        opacity:1
                },100);
                ovr.css({
                        zIndex: 1,
                })
        });
        $(document).on('click', '#overlow-search-result', function(){
                var obj = $('#search-result');
                var objs = $('#search_block');
                var ovr = $('#overlow-search-result');
                objs.removeClass('shadow-bottom-light');
                $('#features1-u').removeClass('blur');
                obj.animate({ 
                        zIndex: '-1',
                        opacity:0
                },100);
                ovr.css({
                        zIndex: '-1',
                })
        })
});	

$(document).ready(function(){
var obj = $('#search_block');
var sRes = $('#search-result');
var offset = obj.offset();
var topOffset = offset.top;
var marginTop = obj.css("marginTop");

$(window).scroll(function() {
var scrollTop = $(window).scrollTop();

  if (scrollTop >= topOffset){
        $('#features1-u').css({
        marginTop: 49,  	
        });
        //obj.addClass('shadow-bottom-light');
    obj.css({
      marginTop: 0,
      top:0,
      width:'100%',
      position: 'fixed',
      zIndex: 100,
    });
    sRes.css({
      marginTop: 0,
      top:53,
      width:'100%',
      position: 'fixed',
    })
  }

  if (scrollTop < topOffset){
        $('#features1-u').css({
                marginTop: 0,  	
        });
        //obj.removeClass('shadow-bottom-light');
        obj.css({
          marginTop: 0,
          position: 'relative',
        });
        sRes.css({
          marginTop: 70,
          position: 'absolute',
        });
      }
    });
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>