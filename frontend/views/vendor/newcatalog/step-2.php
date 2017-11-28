<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\checkbox\CheckboxX;

$this->title = Yii::t('message', 'frontend.views.vendor.add_products', ['ru'=>'Добавить продукты']);
$this->registerCss('
    @media (max-width: 1300px){
       th{
        min-width:110px;
        }
    }');
?>
<section class="content-header">
    <h1 class="margin-right-350">
        <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.vendor.edit_cat_three', ['ru'=>'Редактирование каталога']) ?> <?= '<strong>' . common\models\Catalog::get_value($cat_id)->name . '</strong>' ?>
        <small></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.vendor.catalogs_six', ['ru'=>'Каталоги']),
                'url' => ['vendor/catalogs'],
            ],
            Yii::t('message', 'frontend.views.vendor.edit_cat_five', ['ru'=>'Шаг 2. Редактирование каталога']),
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body">
            <div class="panel-body">
                <ul class="nav fk-tab nav-tabs pull-left">
                    <?= '<li>' . Html::a(Yii::t('message', 'frontend.views.vendor.name_two', ['ru'=>'Название']), ['vendor/step-1-update', 'id' => $cat_id]) . '</li>' ?>
                    <?= '<li class="active">' . Html::a(Yii::t('message', 'frontend.views.vendor.add_goods_three', ['ru'=>'Добавить товары']) . '  <i class="fa fa-fw fa-hand-o-right"></i>', ['vendor/step-2', 'id' => $cat_id]) . '</li>' ?>
                    <?= '<li>' . Html::a(Yii::t('message', 'frontend.views.vendor.change_prices_three', ['ru'=>'Изменить цены']), ['vendor/step-3-copy', 'id' => $cat_id]) . '</li>' ?>
                    <?= '<li>' . Html::a(Yii::t('message', 'frontend.views.vendor.set_for_rest_four', ['ru'=>'Назначить ресторану']), ['vendor/step-4', 'id' => $cat_id]) . '</li>' ?>
                </ul>
                <ul class="fk-prev-next pull-right">
                    <?= '<li class="fk-prev">' . Html::a(Yii::t('message', 'frontend.views.vendor.back', ['ru'=>'Назад']), ['vendor/step-1-update', 'id' => $cat_id]) . '</li>' ?>
                    <?= '<li class="fk-next">' . Html::a('<i class="fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.continue_two', ['ru'=>'Далее']) . ' ', ['vendor/step-3-copy', 'id' => $cat_id]) . '</li>' ?>
                </ul>
            </div>

            <?php
            $gridColumnsBaseCatalog = [
                [
                    'attribute' => 'article',
                    'label' => Yii::t('message', 'frontend.views.vendor.art_eight', ['ru'=>'Артикул']),
                    'value' => 'article',
                    'contentOptions' => ['style' => 'vertical-align:middle;'],
                ],
                [
                    'attribute' => 'product',
                    'label' => Yii::t('message', 'frontend.views.vendor.product_name', ['ru'=>'Наименование']),
                    'format' => 'raw',
                    'value' => function($data) {
                        return Html::decode(Html::decode($data['product']));
                    },
                    'contentOptions' => ['style' => 'vertical-align:middle;width:20%'],
                ],
                [
                    'attribute' => 'units',
                    'label' => Yii::t('message', 'frontend.views.vendor.multiplicity_five', ['ru'=>'Кратность']),
                    'value' => function ($data) {
                        return empty($data['units']) ? '' : $data['units'];
                    },
                    'contentOptions' => ['style' => 'vertical-align:middle;width:120px;'],
                ],
                [
                    'attribute' => 'price',
                    'label' => Yii::t('message', 'frontend.views.vendor.price_seven', ['ru'=>'Цена']),
                    'value' => function ($data) use ($baseCurrencySymbol) {
                        $price = preg_replace('/[^\d.,]/', '', $data['price']);
                        return $price . " ".$baseCurrencySymbol;
                    },
                ],
                [
                    'attribute' => 'ed',
                    'label' => Yii::t('message', 'frontend.views.vendor.measure_five', ['ru'=>'Ед. измерения']),
                    'value' => function ($data) {
                        return $data['ed'];
                    },
                ],
                [
                    'label' => Yii::t('message', 'frontend.views.vendor.category_three', ['ru'=>'Категория']),
                    'value' => function ($data) {
                        $data['category_id'] == 0 ? $category_name = '' : $category_name = \common\models\MpCategory::find()->where(['id' => $data['category_id']])->one()->name;
                        return $category_name;
                    }
                ],
                [
                    'attribute' => 'status',
                    'label' => Yii::t('message', 'frontend.views.vendor.in_stock_four', ['ru'=>'Наличие']),
                    'format' => 'raw',
                    'contentOptions' => ['style' => ''],
                    'value' => function ($data) {
                        $data['status'] == common\models\CatalogBaseGoods::STATUS_OFF ?
                                $product_status = '<span class="text-danger">' . Yii::t('message', 'frontend.views.vendor.nope_three', ['ru'=>'Нет']) . ' </span>' :
                                $product_status = '<span class="text-success">' . Yii::t('message', 'frontend.views.vendor.yep_three', ['ru'=>'Есть']) . ' </span>';
                        return $product_status;
                    },
                ],
                [
                    'label' => Yii::t('message', 'frontend.views.vendor.add_four', ['ru'=>'Добавить']),
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'width:50px;'],
                    'value' => function ($data) use ($cat_id) {

                        $step2AddProductUrl = Url::to(['vendor/step-2-add-product']);
                        
                        $value = common\models\CatalogGoods::searchProductFromCatalogGoods($data['id'], Yii::$app->request->get('id')) ? 1 : 0;
                        $link = CheckboxX::widget([
                                    'name' => 'product_' . $data['id'],
                                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                    'value' => $value,
                                    'autoLabel' => true,
                                    'options' => ['id' => 'product_' . $data['id'], 'data-id' => $data['id'], 'cat-id' => $cat_id, 'value' => $value],
                                    'pluginOptions' => [
                                        'threeState' => false,
                                        'theme' => 'krajee-flatblue',
                                        'enclosedLabel' => true,
                                        'size' => 'lg',
                                    ],
                                    'pluginEvents' => [
                                        'change' => 'function() {
                                 var state = $(this).prop("checked");
                                 var id = $(this).attr("data-id");
                                 var cat_id = $(this).attr("cat-id");
                                 console.log(state);
                                 $.ajax({
                                    url: "' . $step2AddProductUrl . '",
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
                                        'reset' => 'function() { console.log("reset"); }',
                                    ]
                        ]);
//                        $link = CheckboxX::widget([
//                                    'name' => 'test' . $data['id'],
//                                   // 'initInputType' => CheckboxX::INPUT_CHECKBOX,
//                                    'value' => 0,
//                                    'autoLabel' => true,
//                        ]);
                        return $link;
                    },
                ]
            ];
            ?>
            <div class="panel-body">
                <div class="callout callout-fk-info" style="margin-bottom:0">
                    <h4><?= Yii::t('message', 'frontend.views.vendor.step_two', ['ru'=>'ШАГ 2']) ?></h4>

                    <p><?= Yii::t('message', 'frontend.views.vendor.excellent', ['ru'=>'Отлично. Теперь выберите товары для вашего каталога, просто проставив галочки в колонке <strong>Добавить</strong>.']) ?> </p>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <?= Html::input('text', 'search', null, ['class' => 'form-control', 'placeholder' => Yii::t('message', 'frontend.views.vendor.search_four', ['ru'=>'Поиск']), 'id' => 'search']) ?>
                                        <?= ''
//                    CheckboxX::widget([
//                                    'name' => 'test',
//                                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
//                                    'value' => 0,
//                        ]) 
                ?>
                        </div>
                    </div> 
                </div>
            </div>
            <div class="panel-body">
                <?php Pjax::begin(['enablePushState' => false, 'id' => 'pjax-container', 'timeout' => 10000,]) ?>
                <?=
                GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'filterModel' => $searchModel,
                    'filterPosition' => false,
                    'columns' => $gridColumnsBaseCatalog,
                    'options' => ['class' => 'table-responsive'],
                    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'bordered' => false,
                    'striped' => true,
                    'condensed' => false,
                    'responsive' => false,
                    'hover' => false,
                    'resizableColumns' => false,
                ]);
                ?>
                <?php Pjax::end(); ?>
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
        url: "' . Url::to(['vendor/step-2', 'id' => $cat_id]) . '",
        container: "#pjax-container",
        data: {searchString: $("#search").val()}
      })
   }, 700);
});
');
?>
