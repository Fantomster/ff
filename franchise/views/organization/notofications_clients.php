<?php

$this->title = Yii::t('app', 'franchise.views.organization.your_rest', ['ru'=>'Ваши рестораны']);

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\web\View;
use common\assets\CroppieAsset;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);

    $this->registerJs('
        $(".allows").click(function(){
           $.ajax({
                url:location.href,
                data:{id_org:$(this).val()},
                type:"POST",
                success:function(response){
                    console.log("ok!!!");
                },
                error:function(){
                    console.log("Error!!!");
                }
           });
            
        });
    ');
?>


    <section class="content-header">
        <h1>
            <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.organization.your_rest_two', ['ru'=>'Ваши рестораны']) ?>
            <small><?= Yii::t('app', 'franchise.views.organization.rest_info', ['ru'=>'Подключенные Вами рестораны и информация о них']) ?></small>
        </h1>
        <?=
        ''
        //    Breadcrumbs::widget([
        //        'options' => [
        //            'class' => 'breadcrumb',
        //        ],
        //        'links' => [
        //            'Список ваших ресторанов',
        //        ],
        //    ])
        ?>
    </section>
    <section class="content">

        <div class="box box-info order-history">

                <div class="row">
                    <div class="col-md-12">
                        <?=
                        GridView::widget([
                            'id' => 'clientsList',
                            'dataProvider' => $dataProvider,
                            'summary' => '',
                            'options' => ['class' => 'table-responsive'],
                            'tableOptions' => ['class' => 'table table-bordered', 'role' => 'grid'],
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'bordered' => false,
                            'striped' => false,
                            'condensed' => false,
                            'responsive' => false,
                            'hover' => false,
                            'resizableColumns' => false,
                            'export' => [
                                'fontAwesome' => true,
                            ],
                            'columns' => [
                                [
                                    'attribute' => 'id',
                                    'value' => function($model){return $model->organization->id;},
                                    'header' => '№',
                                ],
                                [
                                    'attribute' => 'name',
                                    'value' => function($model){return $model->organization->name;},
                                    'header' => 'Наименование',
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'name',
                                    'value' => function ($data) {
                                        return \common\models\Profile::findOne($data->user->id)->full_name;
                                    },
                                    'header'=>'ФИО Контактов'
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'contact',
                                    'value' => function ($data) {
                                        return $data->organization->phone.'  '.$data->organization->email;
                                    },
                                    'header' => 'Контакты',
                                ],
                                [
                                    'header' => 'Уведомлять о новых заказах(да/нет)',
                                    'cssClass'=>'allows',
                                    'class' => 'yii\grid\CheckboxColumn', 'checkboxOptions' => function($model, $key, $index, $column) {
                                    if(\common\models\RelationUserOrganization::findOne(['user_id'=>Yii::$app->user->id,'organization_id'=>$model->organization->id]))
                                    {
                                        $var = \common\models\notifications\EmailNotification::findOne(['rel_user_org_id'=>\common\models\RelationUserOrganization::findOne(['user_id'=>Yii::$app->user->id,'organization_id'=>$model->organization->id])->id]);
                                        $notif = $var ? $var->order_created: 0;
                                    }else{
                                        $notif = null;
                                    }
                                    return $notif ? ['checked' => "checked", 'value'=>$model->organization->id] : ['value'=>$model->organization->id];
                                },
                                ],
                            ],
                        ]);
                        ?>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <?php
        Modal::begin([
            'id' => 'clientInfo',
        ]);
        ?>
        <?php Modal::end(); ?>
    </section>
<?php
$url = Url::to(['organization/clients']);
$analyticsCurrencyUrl = Url::to(['organization/ajax-update-currency']);
$customJs = <<< JS


$(document).on("change", "#dateFrom,#dateTo", function () {   
var filter_from_date =  $("#dateFrom").val();
var filter_to_date =  $("#dateTo").val();        
    $.pjax({
     type: 'GET',
     push: true,
     timeout: 10000,
     url: "$analyticsCurrencyUrl",
     container: "#alCurrencies",
     data: {
         filter_from_date: filter_from_date,
         filter_to_date: filter_to_date
           }
   }).done(function() {});
});


$(document).on("change", "#filter_currency", function () {
$("#filter_currency").attr('disabled','disabled')      
       
var filter_currency =  $("#filter_currency").val();

    $.pjax({
     type: 'GET',
     push: true,
     timeout: 10000,
     url: "$url",
     container: "#kv-unique-id-1",
     data: {
         filter_currency: filter_currency
           }
   }).done(function() { $("#filter_currency").removeAttr('disabled') });
});

var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

$(document).on("change", '#dateFrom', function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

$(document).on("change", '#dateTo', function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});


JS;
$this->registerJs($customJs, View::POS_READY);
