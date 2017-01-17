<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
?>
<div class="row">
    <div class="col-md-12">
      <h3>Поставщики <small></small></h3>
        <div class="row" id="supplier-block">
            <?php
            foreach($suppliers as $row){
            ?>
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
            <div class="mp-suppiler-block">
              <a href="<?=Url::to(['/site/supplier', 'id' => $row->vendor->id]);?>">
                <img class="supplier-image" src="<?= $row->imageUrl ?>">
              </a>
              <div class="row">
                <div class="col-md-12">
                  <div class="supplier-title">
                    <a href="<?=Url::to(['/site/supplier', 'id' => $row->vendor->id]);?>">
                    <h3><?=$row->vendor->name;?></h3>
                    </a>
                  </div>
                  <div class="supplier-category">
                    <h5><?=!empty($row->vendor->city) ? $row->vendor->city : '&nbsp;';?></h5>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="supplier-button">
                    <a href="#" class="btn btn-success invite-vendor" data-vendor-id="<?= $row->vendor->id ?>" style="width: 100%">Добавить</a>
                  </div>
                </div>
              </div>
            </div>  
          </div>    
            <?php    
            }
            ?> 
        </div>
        <div class="row">
          <div class="col-md-12 min-padding">
            <a href="#" class="btn btn-outline-ajax <?=$suppliersCount>12?'':'disabled'?>" id="product-more">Показать еще</a>  
          </div>   
        </div>
    </div>
</div>

<?php $customJs = <<< JS
var inProgress = false;
var num = 12;
$(window).scroll(function() {
if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
      $('#supplier-more').addClass('disabled');
      $.ajax({
        url: "index.php?r=site/ajax-supplier-more",
        type: "GET",
        data: {"num": num},
        beforeSend: function() {
        inProgress = true;},
        cache: false,
        success: function(response){
            if(response == 0){
               //alert("Больше нет записей");
               $('#product-more').addClass('disabled');
               inProgress = false;
            }else{
               $("#supplier-block").append(response);
               inProgress = false;
               num = num + 6;
               $('#supplier-more').removeClass('disabled');
            }
         }
      });
    }
});
$('#supplier-more').on("click", function (e) {
    e.preventDefault();
    console.log('supplier click more');
    $.ajax({
      url: "index.php?r=site/ajax-supplier-more",
      type: "GET",
      data: {"num": num},
      cache: false,
      success: function(response){
          if(response == 0){
             //alert("Больше нет записей");
          }else{
             $("#supplier-block").append(response);
             num = num + 6;
          }
       }
    });
});       
JS;
$this->registerJs($customJs, View::POS_READY);
?>