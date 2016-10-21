<?php
use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use common\models\Category;
kartik\select2\Select2Asset::register($this);
?>
<?php
$this->title = 'Поставщики';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('');	
?>
<?=
Modal::widget([
    'id' => 'view-catalog',
    'size' => 'modal-lg',
    'clientOptions' => false,   
])
?>
<?=
Modal::widget([
    'id' => 'view-supplier',
    'size' => 'modal-md',
    'clientOptions' => false,   
])
?>
<section class="content-header">
    <h1>
        <i class="fa fa-users"></i> Поставщики
        <small>Список всех ваших поставщиков</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb'
        ],
        'links' => [
            'Поставщики'
        ],
    ])
    ?>
</section>
<section class="content">
<div class="box box-info">
    <!-- /.box-header -->
    <div class="box-body">
        <?php 
        $gridColumnsCatalog = [
            [
            'label'=>'Организация',
            'format' => 'raw',
            'contentOptions' => ['class'=>'text-bold','style' => 'vertical-align:middle;width:45%;font-size:14px'],
            'value'=>function ($data) {
            $res = common\models\Organization::find()->where(['id'=>$data->supp_org_id])->one()->name;
            return Html::a(Html::encode($res), ['client/view-supplier', 'id' => $data->supp_org_id], [
                'data' => [
                'target' => '#view-supplier',
                'toggle' => 'modal',
                'backdrop' => 'static',
                          ],
                ]);
            }
            ],
            [
            'label'=>'Статус сотрудничества',
            'contentOptions' => ['style' => 'vertical-align:middle;width:45%;'],
            'format' => 'raw',
            'value'=>function ($data) {
                if($data->invite==0){ 
                $res = '<span class="text-primary"><i class="fa fa-circle-thin"></i> Ожидается подтверждение</span>';
                }else{
                    if(\common\models\User::find()->where(['email'=>\common\models\Organization::find()->
                        where(['id'=>$data->supp_org_id])->one()->email])->exists())
                        {    
                            $res = '<span class="text-yellow"><i class="fa fa-circle-thin"></i> Подтвержден / Не авторизован</span>';
                        }else{
                            $res = '<span class="text-success"><i class="fa fa-circle-thin"></i> Подтвержден</span> ';
                        }
                    } 
                    return $res;
                },
            ],
            [
            'label'=>'',
            'contentOptions' => ['style' => 'vertical-align:middle;width:10%;min-width:139px;'],
            'format' => 'raw',
            'value'=>function ($data) {
            $cat = common\models\Catalog::find()->where(['id'=>$data->cat_id])->one();
            $data->invite==0 ? $result = '' :
            $result = Html::a('Заказ', ['order/create',
                'OrderCatalogSearch[searchString]'=>"",
                'OrderCatalogSearch[selectedCategory]'=>"",
                'OrderCatalogSearch[selectedVendor]'=>$data->supp_org_id,
                ],[
                    'class'=>'btn btn-outline-success btn-sm',
                    'data-pjax'=>0, 
                    'style'=>'margin-right:10px;text-center'
                  ]);
            $data->invite==0 ? $result .= '' :
            $result .= $data->cat_id==0 ? 'Каталог не назначен' :
                Html::a('Каталог', ['client/view-catalog', 'id' => $data->cat_id], [
                'class'=>'btn btn-default btn-sm',
                'style'=>'text-center',
                'data-pjax'=>0,
                'data' => [
                'target' => '#view-catalog',
                'toggle' => 'modal',
                'backdrop' => 'static',
                   ],
                ]);
            
            return $result;
            }
            ]/*,
            ['attribute' => '',
                'format'=>'raw',
                'contentOptions' => ['class'=>'text-center','style' => 'vertical-align:middle;width:50px;'],
                'value'=>function($data) {
            $data->invite==0 ? $result = '' :
            $result = Html::a('заказ', ['order/create',
                'OrderCatalogSearch[searchString]'=>"",
                'OrderCatalogSearch[selectedCategory]'=>"",
                'OrderCatalogSearch[selectedVendor]'=>$data->supp_org_id,
                ],['class'=>'btn btn-outline-success btn-sm pull-right','data-pjax'=>0]); 
            return $result;
            }]*/
        ];
        ?>
        <div class="panel-body">
            <div class="box-body table-responsive no-padding">
            <?php Pjax::begin(['enablePushState' => false,'timeout' => 10000, 'id' => 'sp-list'])?>
            <?=GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'filterPosition' => false,
                'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
                'columns' => $gridColumnsCatalog, 
                'filterPosition' => false,
                'summary' => '',
                'options' => ['class' => 'table-responsive'],
                'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
            ]);
            ?>  
            <?php Pjax::end(); ?> 
            </div>
        </div>
    </div>
</div>
</section>
<?php           
$customJs = <<< JS
$("body").on("hidden.bs.modal", "#view-supplier", function() {
    $(this).data("bs.modal", null);
    //$.pjax.reload({container: "#sp-list"});
})
$("body").on("hidden.bs.modal", "#view-catalog", function() {
    $(this).data("bs.modal", null);
})
$("#view-supplier").on("click", ".save-form", function() {      
        
    var form = $("#supplier-form");
    $.ajax({
    url: form.attr("action"),
    type: "POST",
    data: form.serialize(),
    cache: false,
    success: function(response) {
        $.pjax.reload({container: "#sp-list"});
            form.replaceWith(response);
                  
        },
        failure: function(errMsg) {
        console.log(errMsg);
    }
    });
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>
