<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;

$this->title = Yii::t('message', 'frontend.views.order.guides.edit_templ', ['ru'=>"Редактирование шаблона"]);

$this->registerJs('
    $(document).on("click", ".select-vendor", function() {
        var clicked = $(this);
        var url = clicked.data("url");
        $.post(
            url
        ).done(function(result) {
            if (result) {
                $(".selected-vendor")
                    .removeClass("selected-vendor")
                    .addClass("select-vendor")
                    .removeClass("disabled")
                    .removeClass("btn-gray")
                    .addClass("btn-success")
                    .html(\'<i class="fa fa-hand-pointer-o"></i> ' . Yii::t('message', 'frontend.views.order.guides.choose_two', ['ru'=>'Выбрать']) . ' \');
                clicked
                    .addClass("selected-vendor")
                    .removeClass("select-vendor")
                    .addClass("disabled")
                    .removeClass("btn-success")
                    .addClass("btn-gray")
                    .html(\'<i class="fa fa-thumbs-o-up"></i> ' . Yii::t('message', 'frontend.views.order.guides.changed_two', ['ru'=>'Выбран']) . ' \');
                $.pjax.reload("#productList", {timeout:30000});
            }
        });
    });
    
    $(document).on("click", ".add-to-guide", function() {
        var clicked = $(this);
        var url = clicked.data("url");
        $.post(
            url
        ).done(function(result) {
            if (result) {
                clicked
                    .addClass("in-guide")
                    .removeClass("add-to-guide")
                    .addClass("disabled")
                    .removeClass("btn-success")
                    .addClass("btn-gray")
                    .html(\'<i class="fa fa-thumbs-o-up"></i> ' . Yii::t('message', 'frontend.views.order.guides.product_added', ['ru'=>'Продукт добавлен']) . ' \');
                $.pjax.reload("#guideProductList", {timeout:30000});
            }
        });
    });
    
    $(document).on("click", ".remove-from-guide", function() {
        var clicked = $(this);
        var url = clicked.data("url");
        $.post(
            url
        ).done(function(result) {
            if (result) {
                clicked.parent().parent().hide();
                $("#" + clicked.data("target-id"))
                    .removeClass("in-guide")
                    .addClass("add-to-guide")
                    .removeClass("disabled")
                    .removeClass("btn-gray")
                    .addClass("btn-success")
                    .html(\'<i class="fa fa-plus"></i> ' . Yii::t('message', 'frontend.views.order.guides.add_to_templ_two', ['ru'=>'Добавить в шаблон']) . ' \');
            }
        });
    });
    
    $(document).on("click", ".btnSubmit", function() {
        $($(this).data("target-form")).submit();
    });
    ', \yii\web\View::POS_READY);
?>
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>"><?= Yii::t('message', 'frontend.views.order.guides.all_products', ['ru'=>'Все продукты']) ?></a></li>
            <li class="active">
                <a href="#">
                    <?= Yii::t('message', 'frontend.views.order.guides.order_templates', ['ru'=>'Шаблоны заказов']) ?> <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="<?= Url::to(['order/favorites']) ?>">
                    <?= Yii::t('message', 'frontend.views.order.guides.freq', ['ru'=>'Часто заказываемые товары']) ?> <small class="label bg-yellow">new</small>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left"><?= Yii::t('message', 'frontend.views.order.guides.choose_vendor', ['ru'=>'Выберите поставщика']) ?></div>
                                    <div class="guid_block_title_l pull-right"><?= Yii::t('message', 'frontend.views.order.guides.step_one', ['ru'=>'ШАГ 1']) ?></div>
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
                                <?=
                                        $form->field($vendorSearchModel, 'search_string', [
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
                                            'placeholder' => Yii::t('message', 'frontend.views.order.guides.search', ['ru'=>'Поиск среди ваших поставщиков'])])
                                        ->label(false)
                                ?>
                                <?php ActiveForm::end(); ?>
                                <?php
                                Pjax::begin(['formSelector' => '#searchGuideForm', 'enablePushState' => false, 'id' => 'vendorList', 'timeout' => 30000]);
                                ?>
                                <?= $this->render('_vendor-list', compact('vendorDataProvider', 'selectedVendor')) ?>
                                <?php Pjax::end(); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left"><?= Yii::t('message', 'frontend.views.order.guides.choose_product', ['ru'=>'Выберите его продукт']) ?></div>
                                    <div class="guid_block_title_l pull-right"><?= Yii::t('message', 'frontend.views.order.guides.step_two', ['ru'=>'ШАГ 2']) ?></div>
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
                                            'placeholder' => Yii::t('message', 'frontend.views.order.guides.products_search', ['ru'=>'Поиск по продуктам выбранного поставщика'])])
                                        ->label(false)
                                ?>
                                <?php ActiveForm::end(); ?>
                                <?php Pjax::begin(['formSelector' => '#searchProductForm', 'enablePushState' => false, 'id' => 'productList', 'timeout' => 30000]); ?>
                                <?= $this->render('_product-list', compact('productDataProvider', 'guideProductList')) ?>
                                <?php Pjax::end(); ?>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left"><?= Yii::t('message', 'frontend.views.order.guides.template', ['ru'=>'Шаблон:']) ?> <?= $guide->name ?></div>
                                    <div class="guid_block_title_l pull-right"><?= Yii::t('message', 'frontend.views.order.guides.step_three', ['ru'=>'ШАГ 3']) ?></div>
                                </div>
                                <?php
                                $form = ActiveForm::begin([
                                            'options' => [
                                                'id' => 'searchGuideProductForm',
                                                'role' => 'search',
                                                'style' => 'height:51px;'
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
                                            'placeholder' => Yii::t('message', 'frontend.views.order.guides.templ_search', ['ru'=>'Поиск по набранному шаблону'])])
                                        ->label(false)
                                ?>
                                <?php ActiveForm::end(); ?>
                                <?php Pjax::begin(['formSelector' => '#searchGuideProductForm', 'enablePushState' => false, 'id' => 'guideProductList', 'timeout' => 30000]); ?>
                                <?= $this->render('_guide-product-list', compact('guideDataProvider', 'guideProductList')) ?>
                                <?php Pjax::end(); ?>
                                <div style="width:100%;">
                                    <div style="width:50%;float:left;padding-right:2px;">
                                        <?= Html::a('<i class="fa fa-save"></i> ' . Yii::t('message', 'frontend.views.order.guides.save', ['ru'=>'Сохранить']), ['order/save-guide', 'id' => $guide->id], ['class' => 'btn btn-md btn-success guide-save']) ?>
                                    </div>
                                    <div style="width:50%;float:left;padding-left:2px;">
                                        <?= Html::a('<i class="fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.order.guides.cancel', ['ru'=>'Отменить']), ['order/reset-guide'], ['class' => 'btn btn-md btn-gray guide-cancel']) ?>
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