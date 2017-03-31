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
</style>
<section class="content">
    <div class="box box-info">
        <!-- /.box-header -->
        <div class="box-body no-padding">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="req-name pull-left"><?=$request->product?></h3>
                        <?= Html::button('Закрыть заявку', ['class' => 'r-close btn btn-sm btn-danger pull-right','data-id'=>$request->id,'style'=>'margin-top: 21px;']) ?>
                        
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
                                      <div class="req-fire"><i class="fa fa-fire" aria-hidden="true"></i> СРОЧНО</div>
                                      <div class=""><?=$author->created_at?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">Объем закупки <span class=""><?=$request->amount?></span></div>
                                <div class="">Периодичность заказа <span class=""><?=$request->regular?></span></div>
                                <div class="">Способ оплаты <span class="">
                                    <?=$request->payment_method == \common\models\Request::NAL ? 
                                    'Наличный расчет':
                                    'Безналичный расчет';?></span>
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
                <?php 
                Pjax::begin([
                  'id' => 'pjax-callback', 
                  'timeout' => 10000, 
                  'enablePushState' => false,
                  ]);
                ?>
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
                            'layout' => "{summary}\n{pager}\n{items}\n{pager}",
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