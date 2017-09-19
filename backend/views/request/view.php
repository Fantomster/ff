<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\grid\GridView;
use kartik\form\ActiveForm;

$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        var timer;
        $(".box-body").on("change", "#statusFilter", function() {
            $("#searchForm").submit();
        });
        $("body").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#searchForm").submit();
                }, 700);
            });
    });
        ');
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
                    'url' => ['request/index'],
                ],
                'Заявка №' . $request->id,
            ],
        ])
        ?>
    </section>
    <section  class="content-header">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3 class="text-success">№<?=$request->id?> <?=$request->product?>
                                        <?php if ($request->rush_order){?>
                                            <span style="color:#d9534f"><i class="fa fa-fire" aria-hidden="true"></i> СРОЧНО</span>
                                        <?php } ?>
                                    </h3>
                                    <h4><?=$request->comment?$request->comment:'<b>Нет информации</b>' ?></h4>
                                </div>
                            </div>
                            <h6><b>Ресторан:</b> <?=$author->name?></h6>
                            <h6><b>Адрес ресторана:</b> <?=$author->address?></h6>

                            <h6><b>Объем закупки:</b> <?=$request->amount?></h6>
                            <h6><b>Категория:</b> <?=$request->categoryName->name ?></h6>
                            <h6><b>Периодичность заказа:</b> <?=$request->regularName?></h6>
                            <h6><b>Способ оплаты:</b> <?=$request->paymentMethodName ?></h6>
                            <h6><b>Отложенный платеж(дней):</b> <?=$request->deferment_payment ?></h6>
                            <h5><?=($request->active_status)?'':'<b style="color: red;">Заявка закрыта</b>' ?></h5>
                            <div class="req-respons">Исполнитель:
                                <?=$request->responsible_supp_org_id ?
                                    '<span style="color:#84bf76;text-decoration:underline">' . $request->vendor->name . '</span>' :
                                    '';
                                ?>
                            </div>
                            <p style="margin:0;margin-top:15px"><b>Создана</b> <?=$request->created_at?></p>
                            <p style="margin:0;margin-bottom:15px"><b>Будет снята</b> <?=$request->end?></p>

                            <div style="margin-top: 9px">
                                <span  data-toggle="tooltip" data-placement="bottom" data-original-title="Кол-во уникальных просмотров поставщиков"><i class="fa fa-eye" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->counter?></span>
                                <span  data-toggle="tooltip" data-placement="bottom" data-original-title="Предложений от поставщиков"><i class="fa fa-handshake-o" style="font-size:19px !important" aria-hidden="true"></i> <?=$request->countCallback?></span>
                            </div>
                        </div>
                        <?php
                        Pjax::begin();
                        ?>
                        <div class="col-md-12">
                            <hr>
                            <h3>Предложения поставщиков</h3>

                            <?php
                            $form = ActiveForm::begin([
                                'options' => [
                                    'id' => 'searchForm',
                                    //'class' => "navbar-form",
                                    'role' => 'search',
                                ],
                            ]);
                            ?>
                            <div class="row">
                                <div class="col-lg-5 col-md-5 col-sm-6">
                                    <?=
                                    $form->field($searchModel, 'searchString', [
                                        'addon' => [
                                            'prepend' => [
                                                'content' => '<i class="fa fa-search"></i>',
                                            ],
                                        ],
                                        'options' => [
                                            'class' => "margin-right-15 form-group",
                                        ],
                                    ])
                                        ->textInput([
                                            'id' => 'searchString',
                                            'class' => 'form-control',
                                            'placeholder' => 'Поиск'])
                                        ->label('Поиск', ['style' => 'color:#555'])
                                    ?>
                                </div>
                            </div>

                            <hr>
                            <?php ActiveForm::end(); ?>
                            <?= GridView::widget([
                                'dataProvider' => $dataCallback,
                                'options'=>[
                                    'class'=>''
                                ],
                                'filterPosition' => false,
                                'summary' => '',
                                'options' => ['class' => 'table-responsive'],
                                'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                                'emptyText' => 'Пока нет ни одного предложения',
                                'columns' => [
                                    [
                                        'attribute' => 'id',
                                        'value' => 'id',
                                        'label' => '№',
                                    ],
                                    [
                                        'attribute' => 'organization.name',
                                        'label' => 'Название поставщика',
                                    ],
                                    [
                                        'attribute' => 'price',
                                        'label' => 'Цена',
                                    ],
                                    [
                                        'format' => 'raw',
                                        'attribute' => 'comment',
                                        'label' => 'Комментарий',
                                    ],
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'template' => '{edit}',
                                        'buttons' => [
                                            'edit' => function ($url,$model) {
                                                $customurl=Yii::$app->getUrlManager()->createUrl(['request/update-callback','id'=>$model['id'], 'request_id'=>$model['request_id']]);
                                                return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-pencil"></span>', $customurl,
                                                    ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                                            },
                                        ],
                                    ],
                                ],
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