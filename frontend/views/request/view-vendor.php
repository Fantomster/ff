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
<style>
.req-name{color:#84bf76;font-size:22px;}
.req-fire{color:#d9534f;font-size:18px;}    
.media{line-height: 2.4;}
.media-heading{font-size:16px;font-weight:bold;letter-spacing:0.02em;}
.req-fire{font-size:14px;font-weight:normal}
.req-client-reg{font-size:12px;color:#828384;font-weight:normal}
.req-client-info{font-size:12px;color:#828384;font-weight:normal}
.req-discription{font-size:14px;font-weight:normal;color:#95989a;margin-bottom:10px}
.req-respons{font-size:12px;color:#828384;font-weight:bold}
.req-vendor-info{font-size: 14px;
    color: #828384;
    font-weight: normal;
    margin-top: 25px;}
.req-vendor-price{font-size:21px;color:#828384;font-weight:normal}
.req-vendor-name{font-size:14px;font-weight:bold;color:#3f3f3e;}
.summary-pages{font-size:12px;font-weight:normal;color:#828384;margin-top:5px;padding-bottom:5px}  
</style>
<section class="content">
    <div class="box box-info">
        <!-- /.box-header -->
        <div class="box-body no-padding">
            <?php 
                Pjax::begin([
                  'id' => 'pjax-callback', 
                  'timeout' => 10000, 
                  'enablePushState' => false,
                  ]);
                ?>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="req-name pull-left"><?=$request->product?></h3>
                        <?php if(!$trueFalseCallback){?>
                        <?= Html::button('Предложить свои услуги', ['class' => 'callback btn btn-sm btn-success pull-right','data-id'=>$request->id,'style'=>'margin-top: 21px;']) ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <hr>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="media">
                                    <div class="media-left">
                                      <img src="<?=$author->pictureUrl?>" class="media-object" style="width:160px">
                                    </div>
                                    <div class="media-body">
                                      <h4 class="media-heading"><?=$author->name?></h4>
                                      <?php if ($request->rush_order){?>
                                      <div class="req-fire"><i class="fa fa-fire" aria-hidden="true"></i> СРОЧНО</div>
                                      <?php } ?>
                                      <div class=""><?=$author->created_at?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">Объем закупки <span class=""><?=$request->amount?></span></div>
                                <div class="">Периодичность заказа <span class=""><?=$request->regularName?></span></div>
                                <div class="">Способ оплаты <span class="">
                                    <?=$request->paymentMethodName?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <hr>
                    <div class="col-md-12">
                        <div class="">Подробное описание:</div>
                        <div class="">
                        <?=$request->comment?$request->comment:'<span style="color:#ccc">Нет информации</span>' ?>
                        </div> 
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="">
                            <div class="">Категория: <span class=""><?=$request->categoryName->name ?></span></div>
                        </div> 
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="">
                            <h5 class="">Исполнитель: 
                              <?=$request->responsible_supp_org_id ? 
                                    '<span style="color:#84bf76;text-decoration:underline">' . $request->organization->name . '</span>' : 
                                    '<span style="color:#ccc;">не назначен</span>';
                              ?>
                            </h5>
                        </div> 
                    </div>
                </div>
                <div class="row">
                    <hr>
                    <div class="col-md-12">
                        <div class="row">
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
                              'class'=>'col-lg-12 list-wrapper inline'
                            ],
                            'layout' => "{summary}\n{pager}\n{items}\n{pager}",
                            'summary' => 'Показано {count} из {totalCount}',
                            'emptyText' => 'Откликов по заявке 0',
                        ])?> 
                        </div>
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