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
        <i class="fa fa-paper-plane"></i> <?= Yii::t('message', 'frontend.views.request.request_no', ['ru'=>'Заявка №']) ?><?=$request->id?>
        <small><?= Yii::t('message', 'frontend.views.request.request_activity', ['ru'=>'Следите за активностью заявки']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru'=>'Главная']), 'url' => '/'],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.request.request_list', ['ru'=>'Список заявок']),
                'url' => ['request/list'],
            ],
            Yii::t('message', 'frontend.views.request.request_no_two', ['ru'=>'Заявка №']) . $request->id,
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
		<h3 class="text-success">№<?=$request->id?> <?=$request->product?>
                <?php if ($request->rush_order){?>
      <span style="color:#d9534f"><i class="fa fa-fire" aria-hidden="true"></i> <?= Yii::t('message', 'frontend.views.request.common', ['ru'=>'СРОЧНО']) ?></span>
      <?php } ?>
                </h3>
                <h4><?=$request->comment?$request->comment:'<b>' . Yii::t('message', 'frontend.views.request.no_info', ['ru'=>'Нет информации']) . ' </b>' ?></h4>
              </div>
            </div>
            <h6><b><?= Yii::t('message', 'frontend.views.request.buying_value', ['ru'=>'Объем закупки:']) ?></b> <?=$request->amount?></h6>
            <h6><b><?= Yii::t('message', 'frontend.views.request.frequency', ['ru'=>'Периодичность заказа:']) ?></b> <?=$request->regularName?></h6>
            <h6><b><?= Yii::t('message', 'frontend.views.request.payment_variant', ['ru'=>'Способ оплаты:']) ?></b> <?=$request->paymentMethodName ?></h6>
            <div class="req-respons"><?= Yii::t('message', 'frontend.views.request.executor', ['ru'=>'Исполнитель:']) ?>
                <?=$request->responsible_supp_org_id ? 
                      '<span style="color:#84bf76;text-decoration:underline">' . $request->vendor->name . '</span>' : 
                      '';
                ?>
            </div>
            <p style="margin:0;margin-top:15px"><b><?= Yii::t('message', 'frontend.views.request.created', ['ru'=>'Создана']) ?></b> <?=Yii::$app->formatter->format($request->created_at, 'datetime')?></p>
            <p style="margin:0;margin-bottom:15px"><b><?= Yii::t('message', 'frontend.views.request.will_delete', ['ru'=>'Будет снята']) ?></b> <?=Yii::$app->formatter->format($request->end, 'datetime')?></p>
            <?php if(!$trueFalseCallback){?>
                        <?= Html::button(Yii::t('message', 'frontend.views.request.service', ['ru'=>'Предложить свои услуги']),
                                ['class' => 'callback btn btn-success',
                                 'data-id'=>$request->id]) ?>
                        <?php } ?>
            <div class="pull-right" style="margin-top: 9px">
                <span  data-toggle="tooltip" data-placement="bottom" data-original-title="<?= Yii::t('message', 'frontend.views.request.amount', ['ru'=>'Кол-во уникальных просмотров поставщиков']) ?>"><i class="fa fa-eye" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->counter?></span>
                <span  data-toggle="tooltip" data-placement="bottom" data-original-title="<?= Yii::t('message', 'frontend.views.request.vendors_offers', ['ru'=>'Предложений от поставщиков']) ?>"><i class="fa fa-handshake-o" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->countCallback?></span>
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
                    'summary' => '',
                    'emptyText' => '<h5>' . Yii::t('message', 'frontend.views.request.offer', ['ru'=>'Предложите свою цену заявки! Находите новых, честных партнеров для своего бизнеса!']) . ' </h5><small>' . Yii::t('message', 'frontend.views.request.self_offer', ['ru'=>'Вы можете видеть только свое предложение']) . ' </small>',
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
    title: "' . Yii::t('message', 'frontend.views.request.price', ['ru'=>'Цена']) . ' ",
    text: "' . Yii::t('message', 'frontend.views.request.set_price', ['ru'=>'Установите цену услуги по данной заявке']) . ' ",
    input: "text",
    animation: true,
    confirmButtonText: "' . Yii::t('message', 'frontend.views.request.continue', ['ru'=>'Далее']) . ' ",
    cancelButtonText: "' . Yii::t('message', 'frontend.views.request.cancel', ['ru'=>'Отмена']) . ' ",
    showLoaderOnConfirm: true,
    preConfirm: function (price) {
    return new Promise(function (resolve, reject) {  
        if (!price.match(/^\s*-?[1-9]\d*(\.\d{1,2})?\s*$/)) {
            reject("' . Yii::t('message', 'frontend.views.request.wrong_format', ['ru'=>'Неверный формат! Пример: 1220 , 1220.30']) . ' ");
        }
        resolve()  
      })
    }
  },
  {
    title: "' . Yii::t('message', 'frontend.views.request.comment', ['ru'=>'Комментарий']) . ' ",
    text: "' . Yii::t('message', 'frontend.views.request.enter_comment', ['ru'=>'Оставьте комментарий по заявке']) . ' ",
    input: "textarea",
    animation: false,
    confirmButtonText: "' . Yii::t('message', 'frontend.views.request.send', ['ru'=>'Отправить']) . ' ",
    cancelButtonText: "' . Yii::t('message', 'frontend.views.request.cancel_two', ['ru'=>'Отмена']) . ' ",
    showLoaderOnConfirm: true,
    preConfirm: function (comment) {
    return new Promise(function (resolve, reject) {
      resolve() 
      })
    }
  },
]
swal.queue(steps).then(function (result) {
    console.log(JSON.stringify(result));
    $.ajax({
    url: "' . Url::to(["request/add-callback"]) . '",
    type: "POST",
    dataType: "json",
    data: "id=" + id +"&price=" + result.value[0] + "&comment=" + result.value[1],
    cache: false,
    success: function (response) {
        $.pjax.reload({container:"#pjax-callback", async:false});
        initMap();
        if(response["success"]){
            swal({
            title: "' . Yii::t('message', 'frontend.views.request.sent', ['ru'=>'Отправлено!']) . ' ",
            type: "success",
            progressSteps: false,
            confirmButtonText: "' . Yii::t('message', 'frontend.views.request.close', ['ru'=>'Закрыть']) . ' ",
            showCancelButton: false
          })
          }else{
            swal({
            title: "' . Yii::t('error', 'frontend.views.request.error', ['ru'=>'Ошибка!']) . ' ",
            text: "' . Yii::t('error', 'frontend.views.request.contact_us', ['ru'=>'Свяжитесь с нами для скорейшего устранения данной ошибки!']) . ' ",
            type: "error",
            progressSteps: false,
            confirmButtonText: "' . Yii::t('message', 'frontend.views.request.close', ['ru'=>'Закрыть']) . ' ",
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