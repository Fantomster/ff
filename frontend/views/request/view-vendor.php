<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
?>

<style>
.bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-paper-plane"></i> Заявка №<?=$request->id?>
        <small>Следите за активностью заявки</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Список заявок',
                'url' => ['request/list'],
            ],
            'Заявка №' . $request->id,
        ],
    ])
    ?>
</section>
<section  class="content-header">
  <div class="row">
    <div class="col-md-12">
      <?php 
        Pjax::begin([
          'id' => 'pjax-callback', 
          'timeout' => 10000, 
          'enablePushState' => false,
          ]);
      ?>
      <div class="box box-info">
        <div class="box-body">
	  <div class="col-md-6">
            <div class="row">
              <div class="col-md-12">
		<h3 class="text-success"><?=$request->product?>
                <?php if ($request->rush_order){?>
      <span style="color:#d9534f"><i class="fa fa-fire" aria-hidden="true"></i> СРОЧНО</span>
      <?php } ?>
                </h3>
                <h4><?=$request->comment?$request->comment:'<b>Нет информации</b>' ?></h4>
              </div>
            </div>
            <h6><b>Объем закупки:</b> <?=$request->amount?></h6>
            <h6><b>Периодичность заказа:</b> <?=$request->regularName?></h6>
            <h6><b>Способ оплаты:</b> <?=$request->paymentMethodName ?></h6>
            <div class="req-respons">Исполнитель: 
                <?=$request->responsible_supp_org_id ? 
                      '<span style="color:#84bf76;text-decoration:underline">' . $request->organization->name . '</span>' : 
                      '';
                ?>
            </div>
            <p style="margin:0;margin-top:15px"><b>Создана</b> <?=$request->created_at?></p>
            <p style="margin:0;margin-bottom:15px"><b>Будет снята</b> <?=$request->end?></p>
            <?php if(!$trueFalseCallback){?>
                        <?= Html::button('Предложить свои услуги', 
                                ['class' => 'callback btn btn-success',
                                 'data-id'=>$request->id]) ?>
                        <?php } ?>
            <div class="pull-right" style="margin-top: 9px">
                  <i class="fa fa-eye" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->counter?>
                  <i class="fa fa-handshake-o" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->countCallback?>
		</div>
	  </div>
          <div class="col-md-6">
              <h3 class="text-success"><?=$author->name?></h3>
              <h4><?=$author->address?></h4>
              <div id="map"></div>
              
          </div>
	  <div class="col-md-12">
	    <hr>
                
                <?=ListView::widget([
                    'dataProvider' => $dataCallback,
                    'itemView' => function ($model, $key, $index, $widget) {
                        return $this->render('view/_vendorCBView', ['model' => $model]);
                        },
                    'pager' => [
                        'maxButtonCount' => 5,
                            'options' => [
                            'class' => 'pagination col-md-12'
                        ],
                    ],
                    'options'=>[
                      'class'=>''
                    ],
                    'layout' => "\n{items}\n<div class='pull-left'>{pager}</div><div class='pull-right summary-pages'>{summary}</div>",
                    'summary' => 'Показано {count} из {totalCount}',
                    'emptyText' => 'Откликов по заявке 0',
                ])?>
	  </div>
        </div>
      </div>
      <?php Pjax::end(); ?>
    </div>
  </div>
</section>
<script>
function initMap() {
  var position = {lat: <?=$author->lat?>, lng: <?=$author->lng?>};
  var map = new google.maps.Map(document.getElementById("map"), {
    zoom: 16,
    center: position
  });
  var marker = new google.maps.Marker({
    position: position,
    map: map
  });
}
</script>
<?php
  $gpJsLink= 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
        'key'=>Yii::$app->params['google-api']['key-id'],
        'language'=>Yii::$app->params['google-api']['language'],
        'callback'=>'initMap'
    ));
  $this->registerJsFile($gpJsLink, ['depends' => [yii\web\JqueryAsset::className()],'async'=>true,'defer'=>true]);
?>
<?=$this->registerJs('
$(document).on("click",".callback", function(e){
id = $(this).attr("data-id");
swal.setDefaults({
  showCancelButton: true,
  progressSteps: ["1", "2"]
})
var steps = [
  {
    title: "Цена",
    text: "Установите цену услуги по данной заявке",
    input: "text",
    animation: true,
    confirmButtonText: "Далее",
    cancelButtonText: "Отмена",
    showLoaderOnConfirm: true,
    preConfirm: function (price) {
    return new Promise(function (resolve, reject) {  
        if (!price.match(/^\s*-?[1-9]\d*(\.\d{1,2})?\s*$/)) {
            reject("Неверный формат! Пример: 1220 , 1220.30");
        }
        resolve()  
      })
    }
  },
  {
    title: "Комментарий",
    text: "Оставьте комментарий по заявке",
    input: "textarea",
    animation: false,
    confirmButtonText: "Отправить",
    cancelButtonText: "Отмена",
    showLoaderOnConfirm: true,
    preConfirm: function (comment) {
    return new Promise(function (resolve, reject) {
      resolve() 
      })
    }
  },
]
swal.queue(steps).then(function (result) {
    $.ajax({
    url: "' . Url::to(["request/add-callback"]) . '",
    type: "POST",
    dataType: "json",
    data: "id=" + id +"&price=" + result[0] + "&comment=" + result[1],
    cache: false,
    success: function (response) {
        $.pjax.reload({container:"#pjax-callback", async:false});
        initMap();
        if(response["success"]){
            swal({
            title: "Отправлено!",
            type: "success",
            progressSteps: false,
            confirmButtonText: "Закрыть",
            showCancelButton: false
          })
          }else{
            swal({
            title: "Ошибка!",
            text: "Свяжитесь с нами для скорейшего устранения данной ошибки!",
            type: "error",
            progressSteps: false,
            confirmButtonText: "Закрыть",
            showCancelButton: false
          })
          }
        }
    });
}, function () {
  swal.resetDefaults()
})
})
');?>