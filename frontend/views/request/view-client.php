<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
?>
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
            <div class="col-md-12">
                <?php 
                Pjax::begin([
                  'id' => 'pjax-callback', 
                  'timeout' => 10000, 
                  'enablePushState' => false,
                  ]);
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="req-name pull-left"><?=$request->product?></h3>
                        <?php if ($request->active_status){
                        echo Html::button('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;&nbsp;Закрыть заявку', ['class' => 'r-close btn btn-sm btn-outline-danger pull-right','data-id'=>$request->id,'style'=>'margin-top: 21px;']);
                        }else{
                        echo Html::button('Закрыта', ['disabled'=>true,'class' => 'btn btn-sm btn-outline-danger pull-right','data-id'=>$request->id,'style'=>'margin-top: 21px;']);
                        }
                        ?>
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
                                      <div class="req-respons">Исполнитель: 
                                        <?=$request->responsible_supp_org_id ? 
                                              '<span style="color:#84bf76;text-decoration:underline">' . $request->organization->name . '</span>' : 
                                              '<span style="color:#ccc;">не назначен</span>';
                                        ?>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right" style='line-height: 2.4;'>
                                <div class="req-client-info">Объем закупки: <span class="text-bold"><?=$request->amount?></span></div>
                                <div class="req-client-info">Периодичность заказа: <span class="text-bold"><?=$request->regularName?></span></div>
                                <div class="req-client-info">Способ оплаты: <span class="text-bold">
                                    <?=$request->paymentMethodName ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <hr>
                    <div class="col-md-12">
                        <div class="req-discription">
                        <?=$request->comment?$request->comment:'<b>Нет информации</b>' ?>
                        </div> 
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="req-client-reg">Категория: <b><?=$request->categoryName->name ?></b></div>
                    </div>
                </div>
                
                <div class="row">
                    <hr>
                    <div class="col-md-12">
                        <div class="row">
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
                              'class'=>'col-lg-12 list-wrapper inline'
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
    </div>
</section>
<?=$this->registerJs('
$(document).on("click",".change", function(e){
var id = $(this).attr("data-req-id");
var suppId = $(this).attr("data-supp-id");
var eNames;
if($(this).attr("data-event")=="appoint"){
    eNames = {title:"Назначить исполнителем?",confirmButtonText:"Назначить",end:"Назначен исполнитель!"};
}
if($(this).attr("data-event")=="exclude"){
    eNames = {title:"Исключить исполнителя?",confirmButtonText:"Исключить",end:"Исключен!"};
}
swal({
  title: eNames["title"],
  text: false,
  type: "warning",
  showCancelButton: true,
  cancelButtonText: "Отмена",
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
  title: "Закрыть заявку?",
  text: "Заявка будет будет удалена из списка заявок",
  type: "warning",
  showCancelButton: true,
  cancelButtonText: "Отмена",
  confirmButtonText: "Закрыть",
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
            resolve()
            }
        });
    })
  }
}).then(function () {swal("Готово!","Заявка закрыта","success")
})
});
');?>