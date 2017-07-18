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
use yii\helpers\ArrayHelper;
use kartik\checkbox\CheckboxX;
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
use yii2assets\fullscreenmodal\FullscreenModal;
use delocker\animate\AnimateAssetBundle;
AnimateAssetBundle::register($this);
yii2assets\fullscreenmodal\FullscreenModalAsset::register($this);
use kartik\select2\Select2;
kartik\select2\Select2Asset::register($this);
$request = new \common\models\Request();
$this->title = 'Заявки';
?>
<style>
    .req-items{
    background: #fff;
    border-bottom: 1px solid #f4f4f4;
    position: relative;
    padding: 10px;
    margin-top:10px;
    }
    .req-items:hover, .req-name:hover{
    border-bottom:1px solid #84bf76;
    cursor:pointer
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
.select2-container .select2-selection--single .select2-selection__rendered {
    padding-right: 0px;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    margin-top: 0px;
}
.select2-container--krajee .select2-selection__clear {
    line-height: 1;
}
.select2-container--krajee .select2-selection--single .select2-selection__arrow {
    border: none;
    border-left: none;
}
.req-name{font-size:16px;font-weight:bold;letter-spacing:0.02em;}
.req-fire{font-size:14px;font-weight:normal}
.req-cat{font-size:12px;font-weight:normal;color:#828384}
.req-cat-name{font-size:12px;font-weight:bold;color:#828384}
.req-nal-besnal{font-size:12px;font-weight:bold;color:#828384}
.summary-pages{font-size:12px;font-weight:normal;color:#828384;margin-top:27px}
.req-discription{font-size:14px;font-weight:normal;color:#95989a}
.req-created{font-size:12px;font-weight:normal;color:#828384;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-paper-plane"></i> Список заявок
        <small>Находите честных партнеров для своего бизнеса</small>
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
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <div class="box box-info">
            <div class="box-body no-padding" style="padding-bottom:15px !important">
              <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                      <?= Html::label('&nbsp;', null, ['class' => 'label','style'=>'color:#555']) ?>
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
              <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                    <?= Html::label('Категория', null, ['class' => 'label','style'=>'color:#555']) ?>
                    <?=Select2::widget([
                        'name' => 'category',
                        'value' => '',
                        'data' => ArrayHelper::map(\common\models\MpCategory::find()->where(['parent'=>null])->orderBy('name')->all(),'id','name'),
                        'options' => ['id'=>'category','multiple' => false, 'placeholder' => 'Категория товара'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]);
                    ?>
              </div>
              <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                    <?= Html::label('Все заявки/мои', null, ['class' => 'label','style'=>'color:#555']) ?>
                    <?=Select2::widget([
                        'name' => 'my-only',
                        'value' => '',
                        'data' => [1=>'Все',2=>'Только мои'],
                        'options' => ['id'=>'my-only','multiple' => false, 'placeholder' => false],
                        'hideSearch' => true,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]);
                    ?>
              </div>
              <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                    <?= Html::label('Все заявки/срочные', null, ['class' => 'label','style'=>'color:#555']) ?>
                    <?=Select2::widget([
                        'name' => 'rush',
                        'value' => '',
                        'data' => [1=>'Все',2=>'Срочные'],
                        'options' => ['id'=>'rush','multiple' => false, 'placeholder' => false],
                        'hideSearch' => true,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]);
                    ?>
              </div>
            </div>
          </div>
            <?php echo Html::img('@web/images/banner-240x400.gif', ['class' => 'img-responsive hidden-xs hidden-sm hidden-md']) ?>
        </div>
      </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
      <div class="row">
        <div class="col-md-12">   
          <div class="box box-info">
            <div class="box-body" style="padding-bottom:15px !important;padding-top:0 !important;">
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
                    'layout' => "\n{items}\n<div class='pull-left'>{pager}</div><div class='pull-right summary-pages'>{summary}</div>",
                    'summary' => 'Показано {count} из {totalCount}',
                    'emptyText' => 'Список пуст',
                ])?>
              <?php Pjax::end(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-2">
          <?php echo Html::img('@web/images/banner-240x400.gif', ['class' => 'img-responsive hidden-xs', 'style'=>'margin-bottom:15px']) ?>
          <?php echo Html::img('@web/images/banner-240x400.gif', ['class' => 'img-responsive hidden-xs']) ?>
    </div>
  </div>
</section>
<?php

$this->registerJs('
var timer;
$("#search,#my-only,#category,#rush").on("keyup put paste change", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: "GET",
        push: true,
        url: "' . Url::to(["request/list"]) . '",
        container: "#list",
        data: { search: $("#search").val(), myOnly: $("#my-only").val(), category: $("#category").val(), rush: $("#rush").val()}
      });
   }, 700);
});
$("body").on("hidden.bs.modal", "#create", function() {
    $.pjax.reload({container:"#pjax-create", async:false});
    
});
$(document).on("click", ".req-items", function() {
    var id = $(this).attr("data-id");
    var url = "' . Url::to(["request/view", 'id' => '']) . '" + id;
    window.location.href = url;
})  

');
?>
