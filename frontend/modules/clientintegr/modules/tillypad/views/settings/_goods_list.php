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

$dataProvider = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['org_id' => $org, 'is_active' => '1']));
$arrSession = Yii::$app->session->get('SelectedProduct');
$iikoSelectedGoods = \api\common\models\iiko\iikoSelectedProduct::findAll(['organization_id' => $org]);
$arr = [];
if ($iikoSelectedGoods) {
    foreach ($iikoSelectedGoods as $good) {
        $arr[] = $good->product_id;
    }
}
if (is_array($arrSession)) {
    $arr = array_merge($arr, $arrSession);
}

Pjax::begin(['id' => 'pjax-vsd-list', 'timeout' => 15000, 'scrollTo' => true, 'enablePushState' => true]);
$form = ActiveForm::begin([
    'options'                => [
        'data-pjax' => true,
        'id'        => 'search-form',
        'role'      => 'search',
    ],
    'enableClientValidation' => false,
    'method'                 => 'get',
]);
?>
    <div class="row">
        <div class="col-md-3">
            <?php
            echo $form->field($searchModel, 'product_type')->dropDownList(array_merge(['all' => 'Все'], $searchModel->getProductType()), ['value' => $productSearch])->label(Yii::t('app', 'Тип продукта'), ['class' => 'label', 'style' => 'color:#555']);
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->field($searchModel, 'cooking_place_type')->dropDownList(array_merge(['all' => 'Все'], $searchModel->getCoockingPlaceType()), ['value' => $cookingPlaceSearch])
                ->label(Yii::t('app', 'Тип места приготовления продукта'), ['class' => 'label', 'style' => 'color:#555']);
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->field($searchModel, 'unit')
                ->dropDownList(array_merge(['all' => 'Все'], $searchModel->getUnit()), ['value' => $unitSearch])->label(Yii::t('app', 'Единица измерения товара в системе Tillypad'), ['class' => 'label', 'style' => 'color:#555']);
            ?>
        </div>
    </div>
<?php echo Html::hiddenInput('selected_goods'); ?>
<?php
echo \kartik\grid\GridView::widget([
    'dataProvider'     => $dataProvider,
    'filterModel'      => $searchModel,
    'columns'          => [
        [
            'class'           => 'kartik\grid\CheckboxColumn',
            'contentOptions'  => ['class' => 'small_cell_checkbox'],
            'headerOptions'   => ['style' => 'text-align:center; '],
            'checkboxOptions' => function ($model, $key, $index, $widget) use ($arr) {
                if (is_iterable($arr) && in_array($model->id, $arr)) {
                    $checked = true;
                } else {
                    $checked = false;
                }
                echo Html::hiddenInput('goods[' . $model->id . ']', (int)$checked, ['id' => 'goods_' . $model->id, 'class' => 'alHiddenInput']);
                return ['value' => (int)$checked, 'id' => $model->id, 'class' => 'checkbox-group_operations alHiddenInput', 'checked' => $checked];
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
    'filterPosition'   => false,
    'pjax'             => true,
    'options'          => ['class' => 'table-responsive'],
    'tableOptions'     => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
    'formatter'        => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
    'bordered'         => false,
    'striped'          => true,
    'condensed'        => false,
    'responsive'       => false,
    'hover'            => true,
    'resizableColumns' => false,
    'export'           => [
        'fontAwesome' => true,
    ],
]);
ActiveForm::end();
Pjax::end();
$sessionUrl = \yii\helpers\Url::to('ajax-add-product-to-session');
$url = \Yii::$app->urlManager->createUrl('/clientintegr/tillypad/settings');
$customJs = <<< JS

 /*$("document").ready(function(){
        $(".dict-agent-form").on("click", "button[type='submit']", function() {
            $("#w0").submit();
        });
     });*/

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
 
/*  $("document").ready(function(){
        $(".box-body").on("change", "#filterProductType", function() {
            $("#search-form").submit();
        });
     });  
 
 $("document").ready(function(){
        $(".box-body").on("change", "#recipientFilter", function() {
            $("#search-form").submit();
        });
     });   
 
 $(document).on("click", ".clear_filters", function () {
           $('#product_name').val(''); 
           $('#statusFilter').val(''); 
           $('#typeFilter').val('1');
           $('#dateFrom').val('');
           $('#dateTo').val('');
           $('#recipientFilter').val('');
           $("#search_form").submit();
    });
 
 $(".box-body").on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        }); 
 
 $(document).on("change keyup paste cut", "#product_name", function() {
     if (justSubmitted) {
            clearTimeout(justSubmitted);
        }
        justSubmitted = setTimeout(function() {
            justSubmitted = false;
            $("#search-form").submit();
        }, 700);
    });
*/ 
$("document").ready(function(){
        $(".dict-agent-form").on("click", ".checkbox-group_operations", function() { 
            var productID = $(this).val();
            var idName = /*'goods_' +*/ productID;
            if ($(this).prop('checked')){
                $.ajax({
                    url : '$sessionUrl',
                    type: 'post',
                    data : {productID : productID}
                });
               $('#' + idName).val(1);
            } else {
               $('#' + idName).val(0); 
            }
        });
});

$("document").ready(function(){
        $(".dict-agent-form").on("click", ".select-on-check-all", function() {
            if ($(this).prop('checked')){
                $('.alHiddenInput').val(1);
            } else {
                $('.alHiddenInput').val(0);
            }
        });
});

$("document").ready(function(){
    $(".btn-primary").on("click", function() {
        //var pos0 = $(".summary").text();
        //var pos1 = pos0.split('-');
        //var pos2 = pos1[1];
        //var pos3 = pos2.split(' ');
        //var pos = pos3[0];
        //var ostatok = pos % 20;
        //if (ostatok == 0) {
        //    var page = pos / 20;
        //} else {
        //    var page = Math.floor(pos/20) + 1;
        //}
        //var qasc=$('.asc').attr('data-sort'); //узнаём порядок сортировки с классом asc
        //var qdesc=$('.desc').attr('data-sort'); //узнаём порядок сортировки с классом desc                                                    
        //if (typeof qdesc === 'undefined') {
        //    var sortirov=qasc;
        //} else {
        //    var sortirov=qdesc;
        //} //из двух возможных сортировок существует всегда только одна
        //var sortirov0=sortirov.substring(0,1); //узнаём первый символ сортировки
        //if (sortirov0=='-') {
        //    sortirov=sortirov.substring(1);
        //} else {
        //    sortirov='-'+sortirov;
        //} //и меняем на противоположный порядок сортировки
        //var filter1 = $("#iikoproductsearch-product_type").val(); //узнаём значение фильтра НДС
        //var filter2 = $("#iikoproductsearch-cooking_place_type").val(); //узнаём значение фильтра НДС
        //var filter3 = $("#iikoproductsearch-unit").val(); //узнаём значение фильтра НДС
        //$('input[name="page"]').val(page);
        //$('input[name="sort"]').val(sortirov);
        //$('input[name="filter1"]').val(filter1);
        //$('input[name="filter2"]').val(filter2);
        //$('input[name="filter3"]').val(filter3);
        /*var vr = $("#w0").attr('action');
        var act = '/ru/clientintegr/iiko/settings/change-const?id=7&page='+page+'&sort='+sortirov+'&productSearch='+filter1+'&cookingPlaceSearch='+filter2+'&unitSearch='+filter3;
        $("#w0").attr('action', act);
        $("#w0").submit();*/
        var a = new Map();
        var b,c;
        $('.checkbox-group_operations').each(function() {
            b = $(this).attr('id');
            c = $(this).val();
            a[b] = c;
        });
        $.post('$url/change-selected-products', {goods:a}).done(
            function(data){
                var arr = JSON.parse(data);
                var uspeh = arr[101]['id'];
                var all = arr[101]['val'];
                var s = Object.keys(arr).length;
                delete arr[101];
                if (uspeh=='false') {
                    if (all==0) {
                        $('.alHiddenInput').each(function() {
                            $(this).val(0);
                            $(this).prop('checked', false);
                        })
                        $('tbody tr').each(function() {
                            $(this).removeClass('danger');
                        })
                        $('thead tr th input').val(0);
                        $('thead tr th input').prop('checked', false);
                    }
                    if (all==1) {
                        $('.alHiddenInput').each(function() {
                            $(this).val(1);
                            $(this).prop('checked', true);
                        })
                        $('tbody tr').each(function() {
                            $(this).addClass('danger');
                        })
                        $('thead tr th input').val(1);
                        $('thead tr th input').prop('checked', true);
                    }
                    if (all==2) {
                        $('thead tr th input').val(0);
                        $('thead tr th input').prop('checked', false);
                        var s = Object.keys(arr).length+1;
                        var i;
                        for(i=1; i<s; i++) {
                            var id = arr[i]['id'];
                            var value = arr[i]['val'];
                            $('#'+id).val(value);
                            if (value==1) {
                                $('[data-key='+id+']').addClass('danger');
                                $('#'+id).val(1);
                                $('#'+id).prop('checked', true);
                            } else {
                                $('[data-key='+id+']').removeClass('danger');
                                $('#'+id).val(0);
                                $('#'+id).prop('checked', false);
                            }
                        }
                    }
                }
            }
        )
    })
});

JS;
$this->registerJs($customJs, \yii\web\View::POS_READY);
\yii\helpers\Url::remember();
?>