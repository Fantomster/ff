
<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Category;
use common\models\CatalogBaseGoods;
use kartik\checkbox\CheckboxX;
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
?>
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
    'value'=>'units',
    'contentOptions' => ['style' => 'vertical-align:middle;width:120px;'],    
    ],
    [
    'attribute' => 'category_id',
    'label'=>'Категория',
    'value'=>function ($data) {
    $data['category_id']==0 ? $category_name='':$category_name=Category::get_value($data['category_id'])->name;
    return $category_name;
    },
    'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
    'attribute' => 'price',
    'label'=>'Цена',
    'value'=>'price',
    'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
    'attribute' => 'status',
    'label'=>'Наличие',
    'format' => 'raw',
    'contentOptions' => ['style' => 'vertical-align:middle;width:100px;'],
    'value' => function ($data) {
        $link = CheckboxX::widget([
            'name'=>'status_'.$data['id'],
            'initInputType' => CheckboxX::INPUT_CHECKBOX,
            'value'=>$data['status']==0 ? 0 : 1,
            'autoLabel' => false,
            'options'=>['id'=>'status_'.$data['id'], 'data-id'=>$data['id']],
            'pluginOptions'=>[
                'threeState'=>false,
                'theme' => 'krajee-flatblue',
                'enclosedLabel' => true,
                'size'=>'lg',
                ]
        ]);
        return $link;               
    },
    ],                           
    [
        'attribute' => '',
        'label' => '',
        'format' => 'raw',
        'contentOptions' => ['style' => 'width:50px;'],
        'value' => function ($data) {
            $link = Html::a('<i class="fa fa-pencil m-r-xs"></i>', ['/vendor/ajax-update-product', 'id' => $data['id']], [
                'data' => [
                'target' => '#add-product',
                'toggle' => 'modal',
                'backdrop' => 'static',
                          ],
                'class'=>'btn btn-sm btn-warning'

            ]);
            return $link;
        },

    ],
    [
        'attribute' => '',
        'label' => '',
        'format' => 'raw',
        'contentOptions' => ['style' => 'width:50px;'],
        'value' => function ($data) {
            $link = Html::button('<i class="fa fa-trash m-r-xs"></i>',[
                'class'=>'btn btn-sm btn-danger del-product',
                'data'=>['id'=>$data['id']],
            ]);
            return $link;
        },

    ],
];
?>    

<div class="panel-body">
    <div class="box-body table-responsive no-padding">
    <?=GridView::widget([
        'dataProvider' => $dataProvider,
        'filterPosition' => false,
        'columns' => $gridColumnsBaseCatalog, 
        'tableOptions' => ['class' => 'table no-margin'],
        'options' => ['class' => 'table-responsive'],
        'bordered' => false,
        'striped' => true,
        'condensed' => false,
        'responsive' => false,
        'hover' => false,
        'export' => [
            'fontAwesome' => true,
        ],
    ]);
    ?> 
    </div>
</div>
<?php
$customJs = <<< JS
var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'GET',
        url: 'index.php?r=vendor/filter-base-catalog&id=$currentCatalog',
        container: '#products-list',
        data: {searchString: $('#search').val()}
      })
   }, 700);
});   

JS;
$this->registerJs($customJs, View::POS_READY);
?>