<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ListView;
?>

    <style>
        .bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
    </style>
    <section class="content-header">
        <h1>
            <i class="fa fa-paper-plane"></i> <?= Yii::t('app', 'Заявка №') ?><?=$request->id?>
            <small><?= Yii::t('app', 'Следите за активностью заявки') ?></small>
        </h1>
        <?=
        Breadcrumbs::widget([
            'options' => [
                'class' => 'breadcrumb',
            ],
            'links' => [
                [
                    'label' => Yii::t('app', 'Список заявок'),
                    'url' => ['site/requests'],
                ],
                Yii::t('app', 'Заявка №') . $request->id,
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
                                            <span style="color:#d9534f"><i class="fa fa-fire" aria-hidden="true"></i> <?= Yii::t('app', 'СРОЧНО') ?></span>
                                        <?php } ?>
                                    </h3>
                                    <h4><?=$request->comment?$request->comment:'<b>' . Yii::t('app', 'Нет информации') . ' </b>' ?></h4>
                                </div>
                            </div>
                            <h6><b><?= Yii::t('app', 'Ресторан:') ?></b> <?=$author->name?></h6>
                            <h6><b><?= Yii::t('app', 'Адрес ресторана:') ?></b> <?=$author->address?></h6>

                            <h6><b><?= Yii::t('app', 'Объем закупки:') ?></b> <?=$request->amount?></h6>
                            <h6><b><?= Yii::t('app', 'Категория:') ?></b> <?=$request->categoryName->name ?></h6>
                            <h6><b><?= Yii::t('app', 'Периодичность заказа:') ?></b> <?=$request->regularName?></h6>
                            <h6><b><?= Yii::t('app', 'Способ оплаты:') ?></b> <?=$request->paymentMethodName ?></h6>
                            <h6><b><?= Yii::t('app', 'Отложенный платеж(дней):') ?></b> <?=$request->deferment_payment ?></h6>
                            <h5><?=($request->active_status)?'':'<b style="color: red;">' . Yii::t('app', 'Заявка закрыта') . ' </b>' ?></h5>
                            <div class="req-respons"><?= Yii::t('app', 'Исполнитель:') ?>
                                <?=$request->responsible_supp_org_id ?
                                    '<span style="color:#84bf76;text-decoration:underline">' . $request->vendor->name . '</span>' :
                                    '';
                                ?>
                            </div>
                            <p style="margin:0;margin-top:15px"><b><?= Yii::t('app', 'Создана') ?></b> <?=$request->created_at?></p>
                            <p style="margin:0;margin-bottom:15px"><b><?= Yii::t('app', 'Будет снята') ?></b> <?=$request->end?></p>

                            <div style="margin-top: 9px">
                                <span  data-toggle="tooltip" data-placement="bottom" data-original-title="Кол-во уникальных просмотров поставщиков"><i class="fa fa-eye" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->counter?></span>
                                <span  data-toggle="tooltip" data-placement="bottom" data-original-title="Предложений от поставщиков"><i class="fa fa-handshake-o" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->countCallback?></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <h3><?= Yii::t('app', 'Предложения поставщиков') ?></h3>
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
                                'emptyText' => Yii::t('app', 'Пока нет ни одного предложения'),
                            ])?>
                        </div>
                    </div>
                </div>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </section>

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