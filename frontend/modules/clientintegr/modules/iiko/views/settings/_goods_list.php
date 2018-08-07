<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoPconst */
/* @var $dicConst \api\common\models\iiko\iikoDicconst */
/* @var $form yii\bootstrap\ActiveForm */
$this->registerCss("
    tr:hover{cursor: pointer;}
    #orderHistory a:not(.btn){color: #333;}
    .dataTable a{width: 100%; min-height: 17px; display: inline-block;}
    .select2-container .select2-selection--single .select2-selection__rendered{margin-top:0;!important}
    .select2-selection__clear{display: none;}
        ");
$searchModel = new \api\common\models\iiko\search\iikoProductSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
$iikoPconst = \api\common\models\iiko\iikoPconst::find()->leftJoin('iiko_dicconst', 'iiko_dicconst.id=iiko_pconst.const_id')->where('iiko_dicconst.denom="available_goods_list"')->andWhere('iiko_pconst.org=:org', [':org' => $org])->one();
$arr = Yii::$app->session->get('SelectedProduct');
if ($iikoPconst) {
    $iikoArray = unserialize($iikoPconst->value);
    if (is_array($arr)){
        $arr = array_merge($arr, $iikoArray);
    } else {
        $arr = $iikoArray;
    }
}

Pjax::begin(['id' => 'pjax-vsd-list', 'timeout' => 15000, 'scrollTo' => true, 'enablePushState' => false]);
$form = ActiveForm::begin([
    'options' => [
        'data-pjax' => true,
        'id' => 'search-form',
        'role' => 'search',
    ],
    'enableClientValidation' => false,
    'method' => 'get',
]);
?>
<div class="row">
    <div class="col-md-3">
        <?php
        echo $form->field($searchModel, 'product_type')->dropDownList(array_merge(['all' => 'Все'], $searchModel->getProductType()))->label(Yii::t('app', 'Тип продукта'), ['class' => 'label', 'style' => 'color:#555']);
        ?>
    </div>
    <div class="col-md-3">
        <?php
        echo $form->field($searchModel, 'cooking_place_type')->dropDownList(array_merge(['all' => 'Все'], $searchModel->getCoockingPlaceType()))
            ->label(Yii::t('app', 'Тип места приготовления продукта'), ['class' => 'label', 'style' => 'color:#555']);
        ?>
    </div>
    <div class="col-md-3">
        <?php
        echo $form->field($searchModel, 'unit')
            ->dropDownList(array_merge(['all' => 'Все'], $searchModel->getUnit()))->label(Yii::t('app', 'Единица измерения товара в системе IIKO'), ['class' => 'label', 'style' => 'color:#555']);
        ?>
    </div>
</div>
<?php
echo \kartik\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'contentOptions' => ['class' => 'small_cell_checkbox'],
            'headerOptions' => ['style' => 'text-align:center; '],
            'checkboxOptions' => function ($model, $key, $index, $widget) use ($arr) {
                if (isset($arr) && is_array($arr) && in_array($model->id, $arr)) {
                    $checked = true;
                } else {
                    $checked = false;
                }
                return ['value' => $model->id, 'class' => 'checkbox-group_operations', 'checked' => $checked];
            }
        ],
        'id',
        'denom',
        'org_id',
        'num',
        'code',
        'product_type',
        'cooking_place_type',
        'unit',
        'is_active',
        'created_at',
        'updated_at'
    ],
    'filterPosition' => false,
    'pjax' => true,
    'options' => ['class' => 'table-responsive'],
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
    'bordered' => false,
    'striped' => true,
    'condensed' => false,
    'responsive' => false,
    'hover' => true,
    'resizableColumns' => false,
    'export' => [
        'fontAwesome' => true,
    ],
]);
ActiveForm::end();
Pjax::end();
$sessionUrl = \yii\helpers\Url::to('ajax-add-product-to-session');
$customJs = <<< JS

 $("document").ready(function(){
        $(".dict-agent-form").on("click", "button[type='submit']", function() {
            $("#w0").submit();
        });
     });

 $("document").ready(function(){
        $(".dict-agent-form").on("change", "#iikoproductsearch-product_type", function() {
            var val = $('#iikoproductsearch-product_type').val();
            var currentUrl = $(location).attr('href');
            var url = currentUrl + "&productSearch="+val;
            $.pjax.reload({container: "#w1-pjax", url: url, timeout:30000});
        });
     });
  $("document").ready(function(){
        $(".dict-agent-form").on("change", "#iikoproductsearch-cooking_place_type", function() {
            var val = $('#iikoproductsearch-cooking_place_type').val();
            var currentUrl = $(location).attr('href');
            var url = currentUrl + "&cookingPlaceSearch="+val;
            $.pjax.reload({container: "#w1-pjax", url: url, timeout:30000});
        });
     });
   $("document").ready(function(){
        $(".dict-agent-form").on("change", "#iikoproductsearch-unit", function() {
            var val = $('#iikoproductsearch-unit').val();
            var currentUrl = $(location).attr('href');
            var url = currentUrl + "&unitSearch="+val;
            $.pjax.reload({container: "#w1-pjax", url: url, timeout:30000});
        });
     }); 
  
   $("document").ready(function(){
        $(".dict-agent-form").on("click", ".checkbox-group_operations", function() { 
            if ($(this).prop('checked')){
                var productID = $(this).val();
                $.ajax({
                    url : '$sessionUrl',
                    type: 'post',
                    data : {productID : productID}
                });
            }
        });
     });

JS;
$this->registerJs($customJs, \yii\web\View::POS_READY);
\yii\helpers\Url::remember();
?>



