<?php
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
?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Редактирование каталога <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?></h3>
        <span class="pull-right"><?=Html::a('<i class="fa fa-fw fa-chevron-left"></i>  Вернуться к списку каталогов',['vendor/catalogs'])?></span>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="panel-body">
            <ul class="nav fk-tab nav-tabs pull-left">
                <?='<li>'.Html::a('Название',['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
                <?='<li class="active">'.Html::a('Добавить товары <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-2','id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a('Изменить цены',['vendor/step-3-copy','id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a('Назначить ресторану',['vendor/step-4','id'=>$cat_id]).'</li>'?>
            </ul>
            <ul class="fk-prev-next pull-right">
              <?='<li class="fk-prev">'.Html::a('Назад',['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
              <?='<li class="fk-next">'.Html::a('Сохранить и продолжить',['vendor/step-3-copy','id'=>$cat_id]).'</li>'?>
            </ul>
        </div>
        
        <?php 
        $gridColumnsBaseCatalog = [
            [
            'attribute' => 'article',
            'label'=>'Артикул',
            'value'=>'article',
            'contentOptions' => ['style' => 'vertical-align:middle;'],
            ],
            [
            'attribute' => 'product',
            'label'=>'Наименование',
            'value'=>'product',
            'contentOptions' => ['style' => 'vertical-align:middle;'],
            ],
            [
            'attribute' => 'units',
            'label'=>'Кратность',
            'value'=>function ($data) { return empty($data['units'])?'':$data['units'];},
            'contentOptions' => ['style' => 'vertical-align:middle;width:120px;'],    
            ],
            [
            'attribute' => 'price',
            'label'=>'Цена',
            'value'=>function ($data) {
            $price = preg_replace('/[^\d.,]/','',$data['price']);
            return $price." руб.";
            },
            ],
            [
            'attribute' => 'ed',
            'label'=>'Ед. измерения',
            'value'=>function ($data) { return $data['ed'];},
            ],
            [
            'label'=>'Категория',
            'value'=>function ($data) {
                        $data['category_id']==0 ? $category_name='':$category_name=common\models\Category::get_value($data['category_id'])->name;
                        return $category_name;
                        }
            ],        
            [
            'attribute' => 'status',
            'label'=>'Наличие',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],    
            'value'=>function ($data) {$data['status']==common\models\CatalogBaseGoods::STATUS_OFF?
                    $product_status='<span class="text-danger">Нет</span>':
                    $product_status='<span class="text-success">Есть</span>';
                    return $product_status;
                },
            ],
            [
            'attribute' => 'Добавить',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = CheckboxX::widget([
                            'name'=>'product_'.$data['id'],
                            'initInputType' => CheckboxX::INPUT_CHECKBOX,
                            'value'=>common\models\CatalogGoods::searchProductFromCatalogGoods($data['id'],Yii::$app->request->get('id'))? 1 : 0,
                            'autoLabel' => true,
                            'options'=>['id'=>'product_'.$data['id'], 'data-id'=>$data['id']],
                            'pluginOptions'=>[
                                'threeState'=>false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => true,
                                'size'=>'lg',
                                ]
                        ]);
                        return $link;
                },

            ]
        ];
        ?>
        <div class="panel-body">
            <div class="callout callout-fk-info" style="margin-bottom:0">
                <h4>ШАГ 2</h4>

                <p>Отлично. Теперь выберите товары для вашего каталога, просто проставив галочки в колонке <strong>Добавить</strong>. </p>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4">
                    <?=Html::input('text', 'search', null, ['class' => 'form-control','placeholder'=>'Поиск','id'=>'search']) ?>
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
            'tableOptions' => ['class' => 'table no-margin'],
            'options' => ['class' => 'table-responsive'],
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
        url: "index.php?r=vendor/step-2&id=' . $cat_id . '",
        container: "#pjax-container",
        data: {searchString: $("#search").val()}
      })
   }, 700);
});
/** 
 * Forward port jQuery.live()
 * Wrapper for newer jQuery.on()
 * Uses optimized selector context 
 * Only add if live() not already existing.
*/
if (typeof jQuery.fn.live == "undefined" || !(jQuery.isFunction(jQuery.fn.live))) {
  jQuery.fn.extend({
      live: function (event, callback) {
         if (this.selector) {
              jQuery(document).on(event, this.selector, callback);
          }
      }
  });
}
$(".cbx-container").live("click", function(e) {
    var id = $(this).children("input[type=checkbox]").attr("data-id");
    var state = $(this).children("input[type=checkbox]").prop("checked");
$.ajax({
    url: "index.php?r=vendor/step-2&id='. $cat_id .'",
    type: "POST",
    dataType: "json",
    data: {"add-product":true,"baseProductId":id,"state":state},
    cache: false,
    success: function(response) {
                console.log(response);
                
        },
        failure: function(errMsg) {
        console.log(errMsg);
        }
    });
});
');
?>
