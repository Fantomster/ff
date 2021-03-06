<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap\Modal;
?>

<div class="row">
    <div class="col-md-12">
      <h3><?= Yii::t('message', 'market.views.site.rest.rest', ['ru'=>'Рестораны']) ?> <small></small></h3>
        <div class="row" id="supplier-block">
            <?php
            foreach($restaurants as $row){
            ?>
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
            <div class="mp-suppiler-block">
              <a href="<?=Url::to(['/site/restaurant', 'id' => $row->id]);?>">
                <img class="supplier-image" src="<?= empty($row->picture) ? \common\models\Organization::DEFAULT_RESTAURANT_AVATAR : $row->pictureUrl ?>">
              </a>
              <div class="row">
                <div class="col-md-12">
                  <div class="supplier-title">
                    <a href="<?=Url::to(['/site/restaurant', 'id' => $row->id]);?>">
                    <h3><?=$row->name;?></h3>
                    </a>
                  </div>
                  <div class="supplier-category">
                    <h5><?php if(empty($row->locality)){echo '&nbsp;';}else{echo $row->locality;}?></h5>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="supplier-button">
                    <?=Html::a(Yii::t('message', 'market.views.site.rest.get_service', ['ru'=>'предложить услуги']), ['send-service',
                            'id' => $row->id], [
                            'data' => [
                                'target' => '#sendService',
                                'toggle' => 'modal',
                                'backdrop' => 'static',
                            ],
                            'class' => 'btn btn-success send-service',
                            'style' => 'width:100%',
                    ]);
                    ?>
                    <!--a href="#" class="btn btn-success send-service" data-vendor-id="<?= $row->id ?>" style="width: 100%">предложить услуги</a-->
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
            <a href="#" class="btn btn-outline-ajax <?=$restaurantsCount>12?'':'disabled'?>" id="product-more"><?= Yii::t('message', 'market.views.site.rest.show_more', ['ru'=>'Показать еще']) ?></a>
          </div>   
        </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'sendService',
    'size' => 'modal-md',
    'clientOptions' => false,
]);
Modal::end();
?>
<?php 
$restaurantsMoreUrl = Url::to(['site/ajax-restaurants-more']);

$customJs = <<< JS
var inProgress = false;
var num = 12;
$(window).scroll(function() {
if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
      $('#supplier-more').addClass('disabled');
      $.ajax({
        url: "$restaurantsMoreUrl",
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
      url: "$restaurantsMoreUrl",
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
$("body").on("hidden.bs.modal", "#sendService", function() {
    $(this).data("bs.modal", null);
})
JS;
$this->registerJs($customJs, View::POS_READY);
?>
