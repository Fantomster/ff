<?php

use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;

$this->title = "Редактирование гайда";
?>
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>">Все продукты</a></li>
            <li class="active">
                <a href="#">
                    Гайды заказов <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="<?= Url::to(['order/favorites']) ?>">
                    Избранные <small class="label bg-yellow">new</small>
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
                                    <div class="guid_block_title_r pull-left">Выберите поставщика</div>
                                    <div class="guid_block_title_l pull-right">ШАГ 1</div>
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
                                                    'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
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
                                            'placeholder' => 'Поиск среди ваших поставщиков'])
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
                                    <div class="guid_block_title_r pull-left">Выберите его продукт</div>
                                    <div class="guid_block_title_l pull-right">ШАГ 2</div>
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
                                                    'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
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
                                            'placeholder' => 'Поиск по продуктам выбранного поставщика'])
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
                                    <div class="guid_block_title_r pull-left">Гид: <?= $guide->name ?></div>
                                    <div class="guid_block_title_l pull-right">ШАГ 3</div>
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
                                                    'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
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
                                            'placeholder' => 'Поиск по набранному гиду'])
                                        ->label(false)
                                ?>
                                <?php ActiveForm::end(); ?>
                                <?php Pjax::begin(['formSelector' => '#searchGuideProductForm', 'enablePushState' => false, 'id' => 'guideProductList', 'timeout' => 30000]); ?>
                                <?= $this->render('_guide-product-list', compact('guideDataProvider', 'guideProductList')) ?>
                                <?php Pjax::end(); ?>
                                <div style="width:100%;">
                                    <div style="width:50%;float:left;padding-right:2px;"><button class="btn btn-md btn-success guide-save"><i class="fa fa-save"></i> Сохранить</button></div>
                                    <div style="width:50%;float:left;padding-left:2px;"><button class="btn btn-md btn-gra guide-cancel"><i class="fa fa-ban"></i> Отменить</button></div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>            
        </div>
    </div>
</section>