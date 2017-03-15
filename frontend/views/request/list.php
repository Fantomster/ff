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
.req-name{color:#84bf76;font-size:22px;margin-top:20px}
.req-fire{margin-left:10px;color:#d9534f;font-size:18px;}
.req-nal-besnal{margin-left:10px}
.req-category{}
.req-discription{font-size:18px;color:#757575}
.req-created{font-size:12px;color:#757575}
.req-visits{font-size:12px;color:#757575}
.req-comments{font-size:12px;color:#757575}
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
            <?=
            Modal::widget([
                'id' => 'create',
                'clientOptions' => false,
                'toggleButton' => [
                    'label' => '<i class="fa fa-paper-plane"></i> Разместить заявку',
                    'tag' => 'a',
                    'data-target' => '#create',
                    'class' => 'btn btn-sm btn-fk-success pull-right',
                    'href' => Url::to(['/request/create']),
                ],
            ])
            ?>
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
                  'clientOptions' => ['method' => 'GET']
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
                        'linkOptions'=>['data-pjax'=>true]
                    ],
                    'options'=>[
                      'class'=>'col-lg-12 list-wrapper inline'
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
      }).done(function() { console.log('Success works!') });
   }, 700);
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>