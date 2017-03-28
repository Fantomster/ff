<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii2assets\fullscreenmodal\FullscreenModal;
?>
<style>
    .req-items{
    background: #fff;
    border: 1px solid #e4e5e7;
    border-radius: 2px;
    position: relative;
    padding: 10px;
    margin-top:10px;
    }
    .req-items:hover{
-webkit-box-shadow: 0px 2px 21px -8px rgba(0,0,0,0.75);
-moz-box-shadow: 0px 2px 21px -8px rgba(0,0,0,0.75);
box-shadow: 0px 2px 21px -8px rgba(0,0,0,0.75);
    }
.req-name{color:#84bf76;font-size:22px;margin-top:20px}
.req-fire{margin-left:10px;color:#d9534f;font-size:18px;}
.req-nal-besnal{margin-left:10px}
.req-category{}
.req-discription{font-size:18px;color:#757575}
.req-created{font-size:12px;color:#757575}
.req-visits{font-size:12px;color:#757575}
.req-comments{font-size:12px;color:#757575}
.modal.fade .modal-dialog {
    -webkit-transform: scale(0.1);
    -moz-transform: scale(0.1);
    -ms-transform: scale(0.1);
    transform: scale(0.1);
    top: 300px;
    opacity: 0;
    -webkit-transition: all 0.3s;
    -moz-transition: all 0.3s;
    transition: all 0.3s;
}

.modal.fade.in .modal-dialog {
    -webkit-transform: scale(1);
    -moz-transform: scale(1);
    -ms-transform: scale(1);
    transform: scale(1);
    -webkit-transform: translate3d(0, -300px, 0);
    transform: translate3d(0, -300px, 0);
    opacity: 1;
}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-paper-plane"></i> Список заявок
        <small>Разместите заявку и ее увидят все поставщики системы f-keeper</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Список заявок'
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-header with-border">
            <?php FullscreenModal::begin([
            'id' => 'create',
            'options'=>['class'=>'modal-fs fade modal','tabindex'=>'-1'],
            'clientOptions' => false,
                'toggleButton' => [
                    'label' => '<i class="fa fa-paper-plane"></i> Разместить заявку',
                    'tag' => 'a',
                    'data-target' => '#create',
                    'class'=>'btn btn-sm btn-fk-success pull-right',
                    'href' => Url::to(['/request/create']),
                ],
         ]);?>
         <?php FullscreenModal::end();?> 
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="col-md-12 no-padding">
                <div class="row">
                  <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-addon">
                              <i class="fa fa-search"></i>
                            </span>
                        <?=Html::input('text', 'search', \Yii::$app->request->get('search')?:'',
                                [
                                    'class' => 'form-control',
                                    'placeholder'=>'Поиск',
                                    'id'=>'search'
                                ]);?>
                        </div>
                  </div>
                </div>
            </div>
            <div class="col-md-12 no-padding">
              <h3 class="box-title">Мои заявки</h3> 
              <?php 
              Pjax::begin([
                  'id' => 'list', 
                  'timeout' => 10000, 
                  'enablePushState' => false,
                  ]);
              ?> 
              <?=ListView::widget([
                    'dataProvider' => $dataListRequest,
                    'itemView' => function ($model, $key, $index, $widget) {
                        return $this->render('list/_listView', ['model' => $model]);
                        },
                    'pager' => [
                        'maxButtonCount' => 5,
                            'options' => [
                            'class' => 'pagination col-md-12  no-padding'
                        ],
                    ],
                    'options'=>[
                      'class'=>'col-lg-12 list-wrapper inline no-padding'
                    ],
                    'layout' => "{summary}\n{pager}\n{items}\n{pager}",
                    'summary' => 'Показано {count} из {totalCount}',
                    'emptyText' => 'Список пуст',
                ])?>
              <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</section>
<?php
$customJs = <<< JS
var timer;
$('#search').on("keyup put paste change", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'GET',
        push: true,
        url: 'index.php?r=request/list',
        container: '#list',
        data: { search: $('#search').val()}
      });
   }, 700);
});
$("body").on("hidden.bs.modal", "#create", function() {
    $(this).data("bs.modal", null);       
});   
JS;
$this->registerJs($customJs, View::POS_READY);
?>
