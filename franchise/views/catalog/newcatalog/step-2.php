<?php
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Users;
use dosamigos\switchinput\SwitchBox;
use kartik\checkbox\CheckboxX;
$this->title = 'Добавить продукты';
$this->registerCss('
    @media (max-width: 1300px){
       th{
        min-width:110px;
        }
    }');

?>
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> <?= Yii::t('app', 'Редактирование каталога') ?> <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?>
        <small></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
            'label' => Yii::t('app', 'Каталоги'),
            'url' => ['catalog/index', 'vendor_id'=>$vendor_id],
            ],
            Yii::t('app', 'Шаг 2. Редактирование каталога'),
        ],
    ])
    ?>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-body">
        <div class="panel-body">
            <ul class="nav fk-tab nav-tabs pull-left">
                <?='<li>'.Html::a(Yii::t('app', 'Название'),['catalog/step-1-update', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'?>
                <?='<li class="active">'.Html::a(' ' . Yii::t('app', 'Добавить товары') . '  <i class="fa fa-fw fa-hand-o-right"></i>',['catalog/step-2', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a(Yii::t('app', 'Изменить цены'),['catalog/step-3-copy', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a(Yii::t('app', 'Назначить ресторану'),['catalog/step-4', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'?>
            </ul>
            <ul class="fk-prev-next pull-right">
              <?='<li class="fk-prev">'.Html::a(Yii::t('app', 'Назад'),['catalog/step-1-update', 'vendor_id'=>$vendor_id,'id'=>$cat_id]).'</li>'?>
              <?='<li class="fk-next">'.Html::a('<i class="fa fa-save"></i> ' . Yii::t('app', 'Далее') . ' ',['catalog/step-3-copy', 'vendor_id'=>$vendor_id, 'id'=>$cat_id]).'</li>'?>
            </ul>
        </div>
        
        <?php 
        $gridColumnsBaseCatalog = [
            [
            'attribute' => 'article',
            'label'=>Yii::t('app', 'Артикул'),
            'value'=>'article',
            'contentOptions' => ['style' => 'vertical-align:middle;'],
            ],
            [
            'attribute' => 'product',
            'label'=>Yii::t('app', 'Наименование'),
            'value'=>'product',
            'contentOptions' => ['style' => 'vertical-align:middle;width:20%'],
            ],
            [
            'attribute' => 'units',
            'label'=>Yii::t('app', 'Кратность'),
            'value'=>function ($data) { return empty($data['units'])?'':$data['units'];},
            'contentOptions' => ['style' => 'vertical-align:middle;width:120px;'],    
            ],
            [
            'attribute' => 'price',
            'label'=>Yii::t('app', 'Цена'),
            'value'=>function ($data) {
            $price = preg_replace('/[^\d.,]/','',$data['price']);
            return $price.Yii::t('app', " руб.");
            },
            ],
            [
            'attribute' => 'ed',
            'label'=>Yii::t('app', 'Ед. измерения'),
            'value'=>function ($data) { return $data['ed'];},
            ],
            [
            'label'=>Yii::t('app', 'Категория'),
            'value'=>function ($data) {
                        $data['category_id']==0 ? $category_name='':$category_name=\common\models\MpCategory::find()->where(['id'=>$data['category_id']])->one()->name;
                            return $category_name;
                        }
            ],        
            [
            'attribute' => 'status',
            'label'=>Yii::t('app', 'Наличие'),
            'format' => 'raw',
            'contentOptions' => ['style' => ''],    
            'value'=>function ($data) {$data['status']==common\models\CatalogBaseGoods::STATUS_OFF?
                    $product_status='<span class="text-danger">' . Yii::t('app', 'Нет') . ' </span>':
                    $product_status='<span class="text-success">' . Yii::t('app', 'Есть') . ' </span>';
                    return $product_status;
                },
            ],
            [
            'attribute' => Yii::t('app', 'Добавить'),
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) use($cat_id){
                    
                    $step2AddProductUrl = Url::to(['catalog/step-2-add-product']);
                    
                $link = CheckboxX::widget([
                            'name'=>'product_'.$data['id'],
                            'initInputType' => CheckboxX::INPUT_CHECKBOX,
                            'value'=>common\models\CatalogGoods::searchProductFromCatalogGoods($data['id'],Yii::$app->request->get('id'))? 1 : 0,
                            'autoLabel' => true,
                            'options'=>['id'=>'product_'.$data['id'], 'data-id'=>$data['id'], 'cat-id'=>$cat_id],
                            'pluginOptions'=>[
                                'threeState'=>false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => true,
                                'size'=>'lg',
                                ],
                            'pluginEvents' => [
                                'change'=>'function() {
                                 var state = $(this).prop("checked");
                                 var id = $(this).attr("data-id");
                                 var cat_id = $(this).attr("cat-id");
                                 console.log(state);
                                 $.ajax({
                                    url: "'.$step2AddProductUrl.'",
                                    type: "POST",
                                    dataType: "json",
                                    data: {"add-product":true,"baseProductId":id,"state":state, "cat_id":cat_id},
                                    cache: false,
                                    success: function(response) {
                                                console.log(response);             
                                        },
                                        failure: function(errMsg) {
                                        console.log(errMsg);
                                        }
                                });
                                }',
                                'reset'=>'function() { console.log("reset"); }',
                            ]
                        ]);
                        return $link;
                },

            ]
        ];
        ?>
        <div class="panel-body">
            <div class="callout callout-fk-info" style="margin-bottom:0">
                <h4><?= Yii::t('app', 'ШАГ 2') ?></h4>

                <p><?= Yii::t('app', 'Отлично. Теперь выберите товары для каталога, просто проставив галочки в колонке <strong>Добавить</strong>') ?>. </p>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4">
                      <div class="input-group">
                            <span class="input-group-addon">
                              <i class="fa fa-search"></i>
                            </span>
                    <?=Html::input('text', 'search', null, ['class' => 'form-control','placeholder'=>Yii::t('app', 'Поиск'),'id'=>'search']) ?>
                      </div>
                </div> 
            </div>
        </div>
        <div class="panel-body">
        <?php Pjax::begin(['enablePushState' => false, 'id' => 'pjax-container','timeout' => 10000,])?>
        <?=GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'filterPosition' => false,
            'columns' => $gridColumnsBaseCatalog,
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
            'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
            'bordered' => false,
            'striped' => true,
            'condensed' => false,
            'responsive' => false,
            'hover' => false,
           'resizableColumns'=>false,
        ]);
        ?>
        <?php  Pjax::end(); ?>
        </div>
    </div>    
</div>
</section>
<?php

$this->registerJs('
var timer;
$("#search").on("keyup", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: "GET",
        push: false,
        timeout: 10000,
        url: "'.Url::to(['catalog/step-2', 'vendor_id'=>$vendor_id, 'id' => $cat_id]).'",
        container: "#pjax-container",
        data: {searchString: $("#search").val()}
      })
   }, 700);
});
');
?>
