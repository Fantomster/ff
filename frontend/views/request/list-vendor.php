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
    <div class="box box-info">
        <div class="box-body">
            <div class="col-md-12 no-padding">
                <div class="row">
                  <div class="col-md-4">
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
                  <div class="col-md-4">
                        <?php 
                        echo Select2::widget([
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
                  <div class="col-md-4">
                        <?=CheckboxX::widget([
                            'name' => 'my-only',
                            'initInputType' => CheckboxX::INPUT_CHECKBOX,
                            'autoLabel' => true,
                            'options' => ['id' => 'my-only'],
                            'pluginOptions' => [
                                'threeState' => false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => true,
                                'size' => 'lg',
                            ],
                            'labelSettings' => [
                                'label' => 'Только мои',
                                'position' => CheckboxX::LABEL_RIGHT,
                                'options' =>['style'=>'font-size: 16px;color: #3f3e3e;font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;font-weight: 500;']
                                ]
                        ]);?>
                  </div>
                  
                </div>
            </div>
            <div class="col-md-12 no-padding">
              <h3 class="box-title">Заявки</h3> 
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

$this->registerJs('
var timer;
$("#search,#my-only,#category").on("keyup put paste change", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: "GET",
        push: true,
        url: "' . Url::to(["request/list"]) . '",
        container: "#list",
        data: { search: $("#search").val(), myOnly: $("#my-only").prop("checked"), category: $("#category").val()}
      });
   }, 700);
});
$("body").on("hidden.bs.modal", "#create", function() {
    $.pjax.reload({container:"#pjax-create", async:false});
    
});
$(document).on("click", ".req-items", function() {
    var id = $(this).attr("data-id");
    var url = "' . Url::to(["request/view"]) . '&id=" + id;
    window.location.href = url;
})  

');
?>
