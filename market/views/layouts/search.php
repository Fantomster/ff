<?php
use yii\web\View;
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
<?=$this->render('../site/main/_search_form');?>
        </div>
    </div>
</section>
<?php $customJs = <<< JS
$('#backTop').backTop({
    'position' : 400,
    'speed' : 500,
    'color' : 'white',
});
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
