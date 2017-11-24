<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
$this->title = Yii::t('message', 'frontend.views.request.request_no_two', ['ru'=>'Заявка №']) . $request->id;
?>

<style>
.bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-paper-plane"></i> <?= Yii::t('message', 'frontend.views.request.request_no_three', ['ru'=>'Заявка №']) ?><?=$request->id?>
        <small><?= Yii::t('message', 'frontend.views.request.request_activity_two', ['ru'=>'Следите за активностью заявки']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.request.request_two', ['ru'=>'Список заявок']),
                'url' => ['request/list'],
            ],
            Yii::t('message', 'frontend.views.request.request_no_four', ['ru'=>'Заявка №']) . $request->id,
        ],
    ])
    ?>
</section>
<section  class="content-header">
  <div class="row">
    <?php 
        Pjax::begin([
          'id' => 'pjax-callback', 
          'timeout' => 10000, 
          'enablePushState' => false,
          ]);
      ?>
    <div class="col-md-12">
      <div class="box box-info">
        <div class="box-body">
	  <div class="col-md-6">
            <div class="row">
              <div class="col-md-12">
		<h3 class="text-success">№<?=$request->id?> <?=$request->product?>
                <?php if ($request->rush_order){?>
      <span style="color:#d9534f"><i class="fa fa-fire" aria-hidden="true"></i> <?= Yii::t('message', 'frontend.views.request.common_two', ['ru'=>'СРОЧНО']) ?></span>
      <?php } ?>
                </h3>
                <h4><?=$request->comment?$request->comment:'<b>' . Yii::t('message', 'frontend.views.request.no_info_two', ['ru'=>'Нет информации']) . ' </b>' ?></h4>
              </div>
            </div>
            <h6><b><?= Yii::t('message', 'frontend.views.request.buying_value_two', ['ru'=>'Объем закупки:']) ?></b> <?=$request->amount?></h6>
            <h6><b><?= Yii::t('message', 'frontend.views.request.order_frequency', ['ru'=>'Периодичность заказа:']) ?></b> <?=$request->regularName?></h6>
            <h6><b><?= Yii::t('message', 'frontend.views.request.payment_variant', ['ru'=>'Способ оплаты:']) ?></b> <?=$request->paymentMethodName ?></h6>
            <div class="req-respons"><?= Yii::t('message', 'frontend.views.request.executor_two', ['ru'=>'Исполнитель:']) ?>
                <?=$request->responsible_supp_org_id ? 
                      '<span style="color:#84bf76;text-decoration:underline">' . $request->vendor->name . '</span>' : 
                      '<span style="color:#ccc;">' . Yii::t('message', 'frontend.views.request.not_set', ['ru'=>'не назначен']) . ' </span>';
                ?>
            </div>
            <p style="margin:0;margin-top:15px"><b><?= Yii::t('message', 'frontend.views.request.created_two', ['ru'=>'Создана']) ?></b> <?=$request->created_at?></p>
            <p style="margin:0;margin-bottom:15px"><b><?= Yii::t('message', 'frontend.views.request.will_delete_two', ['ru'=>'Будет снята']) ?></b> <?=$request->end?></p>
            <?php if ($request->active_status){
                echo Html::button('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('message', 'frontend.views.request.off', ['ru'=>'Снять с размещения']), ['class' => 'r-close btn btn-outline-danger','data-id'=>$request->id]);
            }else{
                echo Html::button(Yii::t('message', 'frontend.views.request.req_closed', ['ru'=>'Заявка закрыта']), ['disabled'=>true,'class' => 'btn btn-outline-danger','data-id'=>$request->id]);
            }
            ?>
            <div class="pull-right" style="margin-top: 9px">
                  <span  data-toggle="tooltip" data-placement="bottom" data-original-title="<?= Yii::t('message', 'frontend.views.request.views_amount', ['ru'=>'Кол-во уникальных просмотров поставщиков']) ?>"><i class="fa fa-eye" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->counter?></span>
                <span  data-toggle="tooltip" data-placement="bottom" data-original-title="<?= Yii::t('message', 'frontend.views.request.vendors_offers_two', ['ru'=>'Предложений от поставщиков']) ?>"><i class="fa fa-handshake-o" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->countCallback?></span>
		</div>
	  </div>
          <div class="col-md-6">
              <h3 class="text-success"><?=$author->name?></h3>
              <h4><?=$author->address?> <small><?= Yii::t('message', 'frontend.views.request.change_address', ['ru'=>'Адрес можно изменить в разделе "Настройки"']) ?></small></h4>
              <div id="map"></div>
              
          </div>
	  <div class="col-md-12">
	    <hr>
	    <h5><?= Yii::t('message', 'frontend.views.request.vendors_responses', ['ru'=>'Отклики поставщиков']) ?></h5>
                <?=ListView::widget([
                    'dataProvider' => $dataCallback,
                    'itemView' => function ($model, $key, $index, $widget) {
                        return $this->render('view/_clientCBView', ['model' => $model]);
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
                    'summary' => Yii::t('message', 'frontend.views.request.showed', ['ru'=>'Показано']) . '  {count} ' . Yii::t('message', 'frontend.views.request.from', ['ru'=>'из']) . '  {totalCount}',
                    'emptyText' => Yii::t('message', 'frontend.views.request.zero', ['ru'=>'Откликов по заявке 0']),
                ])?>
	  </div>
        </div>
      </div>
    </div>
    <?php Pjax::end(); ?>
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
$(document).on("click",".change", function(e){
var id = $(this).attr("data-req-id");
var suppId = $(this).attr("data-supp-id");
var eNames;
if($(this).attr("data-event")=="appoint"){
    eNames = {title:"' . Yii::t('message', 'frontend.views.request.set_by_exec', ['ru'=>'Назначить исполнителем?']) . ' ",confirmButtonText:"' . Yii::t('message', 'frontend.views.request.set', ['ru'=>'Назначить']) . ' ",end:"' . Yii::t('message', 'frontend.views.request.exec_set', ['ru'=>'Назначен исполнитель!']) . ' "};
}
if($(this).attr("data-event")=="exclude"){
    eNames = {title:"' . Yii::t('message', 'frontend.views.request.exclude_exec', ['ru'=>'Исключить исполнителя?']) . ' ",confirmButtonText:"' . Yii::t('message', 'frontend.views.request.exclude', ['ru'=>'Исключить']) . ' ",end:"' . Yii::t('message', 'frontend.views.request.excluded', ['ru'=>'Исключен!']) . ' "};
}
swal({
  title: eNames["title"],
  text: false,
  type: "warning",
  showCancelButton: true,
  cancelButtonText: "' . Yii::t('message', 'frontend.views.request.cancel_three', ['ru'=>'Отмена']) . ' ",
  confirmButtonText: eNames["confirmButtonText"],
  showLoaderOnConfirm: true,
  preConfirm: function () {
    return new Promise(function (resolve) {
        $.ajax({
            url: "' . Url::to(["request/set-responsible"]) . '",
            type: "POST",
            dataType: "json",
            data: "responsible_id=" + suppId + "&id=" + id,
            cache: false,
            success: function (response) {
            $.pjax.reload({container:"#pjax-callback", async:false});
            initMap();
            resolve()
            }
        });
    })
  }
}).then(function (e){swal("Готово!",eNames["end"],"success")})
});
$(document).on("click",".r-close", function(e){
id = $(this).attr("data-id");
swal({
  title: "' . Yii::t('message', 'frontend.views.request.clos_req', ['ru'=>'Закрыть заявку?']) . ' ",
  text: "' . Yii::t('message', 'frontend.views.request.will_del', ['ru'=>'Заявка будет будет удалена из списка заявок']) . ' ",
  type: "warning",
  showCancelButton: true,
  cancelButtonText: "' . Yii::t('message', 'frontend.views.request.cancel_four', ['ru'=>'Отмена']) . ' ",
  confirmButtonText: "' . Yii::t('message', 'frontend.views.request.yep', ['ru'=>'Да']) . ' ",
  showLoaderOnConfirm: true,
  preConfirm: function () {
    return new Promise(function (resolve) {
        $.ajax({
            url: "' . Url::to(["request/close-request"]) . '",
            type: "POST",
            dataType: "json",
            data: "id=" + id,
            cache: false,
            success: function (response) {
            $.pjax.reload({container:"#pjax-callback", async:false});
            initMap();
            resolve()
            }
        });
    })
  }
}).then(function () {swal("' . Yii::t('message', 'frontend.views.request.ready', ['ru'=>'Готово!']) . ' ","' . Yii::t('message', 'frontend.views.request.closed', ['ru'=>'Заявка закрыта']) . ' ","success")
})
});
$(document).on("click",".add-supplier", function(e){
request_id = $(this).attr("data-req-id");
supp_org_id = $(this).attr("data-supp-id");
swal({
  title: "' . Yii::t('message', 'frontend.views.request.add_vendor', ['ru'=>'Добавить поставщика?']) . ' ",
  text: "' . Yii::t('message', 'frontend.views.request.send_to_vendor', ['ru'=>'Поставщику будет отправлено приглашение к сотрудничеству']) . ' ",
  type: "warning",
  showCancelButton: true,
  cancelButtonText: "' . Yii::t('message', 'frontend.views.request.cancel_five', ['ru'=>'Отмена']) . ' ",
  confirmButtonText: "' . Yii::t('message', 'frontend.views.request.add', ['ru'=>'Добавить']) . ' ",
  showLoaderOnConfirm: true,
  preConfirm: function () {
    return new Promise(function (resolve) {
        $.ajax({
            url: "' . Url::to(["request/add-supplier"]) . '",
            type: "POST",
            dataType: "json",
            data: "request_id=" + request_id + "&supp_org_id=" + supp_org_id,
            cache: false,
            success: function (response) {
            $.pjax.reload({container:"#pjax-callback", async:false});
            initMap();
            resolve()
            }
        });
    })
  }
}).then(function () {swal("' . Yii::t('message', 'frontend.views.request.ready_two', ['ru'=>'Готово!']) . ' ","' . Yii::t('message', 'frontend.views.request.inv_sent', ['ru'=>'Приглашение отправлено!']) . ' ","success")
})
});
');?>