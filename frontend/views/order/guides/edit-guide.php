<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use common\models\guides\Guide;
use yii\web\View;
use common\models\Organization;
use common\models\search\VendorSearch;
use common\models\search\OrderCatalogSearch;
use common\models\search\BaseProductSearch;
use yii\data\ActiveDataProvider;

/** @var $guide Guide */
/** @var $client Organization */
/** @var $vendorSearchModel VendorSearch */
/** @var $productSearchModel OrderCatalogSearch */
/** @var $guideSearchModel BaseProductSearch */
/** @var $productDataProvider ActiveDataProvider */
/** @var $guideDataProvider ActiveDataProvider */
/** @var $vendorDataProvider ActiveDataProvider */
/** @var $guideProductList array */
/** @var $params array */
/** @var $selectedVendor string */

$this->title = Yii::t('message', 'frontend.views.order.guides.edit_templ', ['ru' => "Редактирование шаблона"]);
$urlGuideEdit = Url::to(['order/edit-guide', 'id' => $guide->id]);
$messVendorChoose = Yii::t('message', 'frontend.views.order.guides.choose_two', ['ru' => 'Выбрать']);
$messVendorChosen = Yii::t('message', 'frontend.views.order.guides.changed_two', ['ru' => 'Выбран']);
$messItemRemoved = Yii::t('message', 'frontend.views.order.guides.add_to_templ_two', ['ru' => 'Добавить в шаблон']);
$messItemAdded = Yii::t('message', 'frontend.views.order.guides.product_added', ['ru' => 'Продукт добавлен']);
$messAllProducts = Yii::t('message', 'frontend.views.order.guides.all_products', ['ru' => 'Все продукты']);
$messOrderGuides = Yii::t('message', 'frontend.views.order.guides.order_templates', ['ru' => 'Шаблоны заказов']);
$messMostOrderes = Yii::t('message', 'frontend.views.order.guides.freq', ['ru' => 'Часто заказываемые товары']);
$messFilter = Yii::t('message', 'frontend.views.order.filter_product', ['ru' => 'Фильтрация товаров']);
$messChooseVendor = Yii::t('message', 'frontend.views.order.guides.choose_vendor', ['ru' => 'Выберите поставщика']);
$messStepOne = Yii::t('message', 'frontend.views.order.guides.step_one', ['ru' => 'ШАГ 1']);
$messSearchVendor = Yii::t('message', 'frontend.views.order.guides.search', ['ru' => 'Поиск среди ваших поставщиков']);
$messChooseProduct = Yii::t('message', 'frontend.views.order.guides.choose_product', ['ru' => 'Выберите его продукт']);
$messStepTwo = Yii::t('message', 'frontend.views.order.guides.step_two', ['ru' => 'ШАГ 2']);
$messSeacrhCatalog = Yii::t('message', 'frontend.views.order.guides.products_search', ['ru' => 'Поиск по продуктам выбранного поставщика']);
$messGuide = Yii::t('message', 'frontend.views.order.guides.template', ['ru' => 'Шаблон:']);
$messStepThree = Yii::t('message', 'frontend.views.order.guides.step_three', ['ru' => 'ШАГ 3']);
$messGuideSearch = Yii::t('message', 'frontend.views.order.guides.templ_search', ['ru' => 'Поиск по набранному шаблону']);
$messGuideSave = Yii::t('message', 'frontend.views.order.guides.save', ['ru' => 'Сохранить']);
$messGuideCancel = Yii::t('message', 'frontend.views.order.guides.cancel', ['ru' => 'Отменить']);

$urlItemAdd = Url::to(['/order/ajax-add-to-guide', 'guideId' => $guide->id, 'productId' => '']);
$urlItemRemove = Url::to(['/order/ajax-remove-from-guide', 'guideId' => $guide->id, 'productId' => '']);

$js = <<< JS

$(document).on("click", ".select-vendor", function () {
    var clicked = $(this);
    var url = clicked.data("url");
    $.post(
        url
    ).done(function (result) {
        if (result) {
            $(".selected-vendor")
                .removeClass("selected-vendor")
                .addClass("select-vendor")
                .removeClass("disabled")
                .removeClass("btn-gray")
                .addClass("btn-success")
                .html('<i class="fa fa-hand-pointer-o"></i>$messVendorChoose');
            clicked
                .addClass("selected-vendor")
                .removeClass("select-vendor")
                .addClass("disabled")
                .removeClass("btn-success")
                .addClass("btn-gray")
                .html('<i class="fa fa-thumbs-o-up"></i>$messVendorChosen');
            $.pjax.reload("#productList", {timeout: 30000});
        }
    });
});

$(document).on("click", ".add-to-guide", function () {
    var clicked = $(this);
    $.post("$urlItemAdd" + clicked.attr("id").replace("product", ""))
        .done(function (result) {
            if (result) {
                clicked
                    .addClass("in-guide")
                    .removeClass("add-to-guide")
                    .addClass("disabled")
                    .removeClass("btn-success")
                    .addClass("btn-gray")
                    .html('<i class="fa fa-thumbs-o-up"></i>$messItemAdded');
                $.pjax.reload("#guideProductList", {timeout: 30000});
            }
        });
});

$(document).on("click", ".remove-from-guide", function () {
    var clicked = $(this);
    $.post("$urlItemRemove" + clicked.attr("data-target-id").replace("product", ""))
        .done(function (result) {
            if (result) {
                clicked.parent().parent().hide();
                $("#" + clicked.data("target-id"))
                    .removeClass("in-guide")
                    .addClass("add-to-guide")
                    .removeClass("disabled")
                    .removeClass("btn-gray")
                    .addClass("btn-success")
                    .html('<i class="fa fa-plus"></i>$messItemRemoved');
            }
        });
});


// -------------------------------------------------------------------------------------------
$(document).on("click", ".btnSubmit", function () {
     $($(this).data("target-form")).submit();
});


$(document).on("change", "#baseproductsearch-sort", function () {
    var sort = $(this).val();
    $.pjax({
        type: "GET",
        push: true,
        timeout: 10000,
        url: "$urlGuideEdit",
        container: "#guideProductList",
        data: {
            sort: sort
        }
    });
    $(document).on("click", ".btnSubmit", function () {
        $($(this).data("target-form")).submit();
    });
});
JS;

$this->registerJs($js, View::POS_READY);


?>
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>"><?= $messAllProducts ?></a></li>
            <li class="active"><a href="#"><?= $messOrderGuides ?>
                    <small class="label bg-yellow">new</small>
                </a></li>
            <li><a href="<?= Url::to(['order/favorites']) ?>"><?= $messMostOrderes ?>
                    <small class="label bg-yellow">new</small>
                </a></li>
            <?php
            $disabled_roles = [
                \common\models\Role::ROLE_RESTAURANT_BUYER,
                \common\models\Role::ROLE_RESTAURANT_JUNIOR_BUYER,
                //\common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR,
            ];
            if ($client->parent_id == null && !in_array(Yii::$app->user->identity->role_id, $disabled_roles)) : ?>
                <li>
                    <a href="<?= Url::to(['order/product-filter']) ?>">
                        <?= $messFilter ?>
                        <small class="label bg-yellow">new</small>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        <div class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left"><?= $messChooseVendor ?></div>
                                    <div class="guid_block_title_l pull-right"><?= $messStepOne ?></div>
                                </div>
                                <?php
                                $form = ActiveForm::begin([
                                    'options' => [
                                        'id' => 'searchGuideForm',
                                        'role' => 'search',
                                        'style' => 'height:51px;'
                                    ],
                                ]);
                                ?>
                                <?= $form->field($vendorSearchModel, 'search_string', [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs btnSubmit" data-target-form="#searchGuideForm"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                    'options' => [
                                        'class' => "form-group",
                                        'style' => "padding:8px;border-top:1px solid #f4f4f4;"
                                    ],
                                ])
                                    ->textInput([
                                        'id' => 'searchString',
                                        'class' => 'form-control',
                                        'placeholder' => $messSearchVendor
                                    ])->label(false)
                                ?>
                                <?php ActiveForm::end(); ?>
                                <?php
                                Pjax::begin(['formSelector' => '#searchGuideForm', 'enablePushState' => false,
                                    'id' => 'vendorList', 'timeout' => 10000]);
                                ?>
                                <?= $this->render('_vendor-list', [
                                    'vendorDataProvider' => $vendorDataProvider,
                                    'selectedVendor' => $selectedVendor,
                                ]) ?>
                                <?php Pjax::end(); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left"><?= $messChooseProduct ?></div>
                                    <div class="guid_block_title_l pull-right"><?= $messStepTwo ?></div>
                                </div>
                                <?php
                                $form = ActiveForm::begin([
                                    'options' => [
                                        'id' => 'searchProductForm',
                                        'role' => 'search',
                                        'style' => 'height:51px;'
                                    ],
                                ]);
                                ?>
                                <?=
                                $form->field($productSearchModel, 'searchString', [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs btnSubmit" data-target-form="#searchProductForm"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                    'options' => [
                                        'class' => "form-group",
                                        'style' => "padding:8px;border-top:1px solid #f4f4f4;"
                                    ],
                                ])
                                    ->textInput([
                                        'id' => 'searchProductString',
                                        'class' => 'form-control',
                                        'placeholder' => $messSeacrhCatalog
                                    ])
                                    ->label(false)
                                ?>

                                <?php ActiveForm::end(); ?>

                                <?php Pjax::begin(['formSelector' => '#searchProductForm', 'enablePushState' => false,
                                    'id' => 'productList', 'timeout' => 10000]); ?>
                                <?= $this->render('_product-list', [
                                    'productDataProvider' => $productDataProvider,
                                    'guideProductList' => $guideProductList
                                ]) ?>
                                <?php Pjax::end(); ?>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left"><?= $messGuide ?> <?= $guide->name ?></div>
                                    <div class="guid_block_title_l pull-right"><?= $messStepThree ?></div>
                                </div>
                                <?php
                                $form = ActiveForm::begin([
                                    'options' => [
                                        'id' => 'searchGuideProductForm',
                                        'role' => 'search',
                                    ],
                                ]);
                                ?>
                                <?=
                                $form->field($guideSearchModel, 'searchString', [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs btnSubmit" data-target-form="#searchGuideProductForm"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                    'options' => [
                                        'class' => "form-group",
                                        'style' => "padding:8px;border-top:1px solid #f4f4f4;"
                                    ],
                                ])
                                    ->textInput([
                                        'id' => 'searchGuideString',
                                        'class' => 'form-control',
                                        'placeholder' => $messGuideSearch])
                                    ->label(false)
                                ?>

                                <?php Pjax::begin(['formSelector' => '#searchGuideProductForm', 'enablePushState' => true,
                                    'id' => 'guideProductList', 'timeout' => 10000]); ?>
                                <?= $this->render('_guide-product-list', [
                                    'form' => $form,
                                    'guideSearchModel' => $guideSearchModel,
                                    'guideDataProvider' => $guideDataProvider,
                                    'sort' => $params['sort'],
                                    'show_sorting' => $params['show_sorting'],
                                ]) ?>
                                <?php Pjax::end(); ?>
                                <?php ActiveForm::end(); ?>
                                <div style="width:100%;">
                                    <div style="width:50%;float:left;padding-right:2px;">
                                        <?= Html::a('<i class="fa fa-save"></i> ' . $messGuideSave,
                                            ['order/save-guide', 'id' => $guide->id],
                                            ['class' => 'btn btn-md btn-success guide-save']) ?>
                                    </div>
                                    <div style="width:50%;float:left;padding-left:2px;">
                                        <?= Html::a('<i class="fa fa-ban"></i> ' . $messGuideCancel,
                                            ['order/reset-guide'], ['class' => 'btn btn-md btn-gray guide-cancel']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>