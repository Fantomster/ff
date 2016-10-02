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
        <button class="btn btn-default btn-sm pull-right" onclick="window.history.back();">Вернуться</button>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <?='<li>'.Html::a('Название',['vendor/step-1-update','id'=>$cat_id]).'</li>'?>
                <?='<li class="active">'.Html::a('Добавить товары',['vendor/step-2','id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a('Изменить цены',['vendor/step-3-copy','id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a('Назначить',['vendor/step-4','id'=>$cat_id]).'</li>'?>
            </ul>
        </div>
        <?php Pjax::begin(['id' => 'pjax-container'])?>
        <?php 
        $gridColumnsBaseCatalog = [
            [
            'label'=>'Артикул',
            'value'=>'article',
            ],
            [
            'label'=>'Список товаров',
            'value'=>'product',
            ],
            [
            'label'=>'Кратность',
            'value'=>'units',
            ],
            [
            'label'=>'Цена',
            'value'=>function ($data) {
            $price = preg_replace('/[^\d.,]/','',$data->price);
            return $price." руб.";
            },
            ],
            [
            'label'=>'Категория',
            'value'=>function ($data) {
                        $data->category_id==0 ? $category_name='':$category_name=common\models\Category::get_value($data->category_id)->name;
                        return $category_name;
                        }
            ],        
            [
            'label'=>'Наличие',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],    
            'value'=>function ($data) {$data->status==common\models\CatalogBaseGoods::STATUS_OFF?
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
                            'name'=>'product_'.$data->id,
                            'initInputType' => CheckboxX::INPUT_CHECKBOX,
                            'value'=>common\models\CatalogGoods::searchProductFromCatalogGoods($data->id,Yii::$app->request->get('id'))? 1 : 0,
                            'autoLabel' => true,
                            'options'=>['id'=>'product_'.$data->id, 'data-id'=>$data->id],
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
        <?=GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterPosition' => false,
            'columns' => $gridColumnsBaseCatalog,
            'tableOptions' => ['class' => 'table no-margin'],
            'options' => ['class' => 'table-responsive'],
            'bordered' => false,
            'striped' => true,
            'condensed' => false,
            'responsive' => false,
            'hover' => false,
        ]);
        ?>
        </div>
    </div>    
</div>
<?php  Pjax::end(); ?>
<?php
$this->registerJs('
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
$("input[type=checkbox]").on("change", function(e) {	
var id = $(this).attr("data-id");
var state = $(this).prop("checked");
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
