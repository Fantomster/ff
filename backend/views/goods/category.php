<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\depdrop\DepDrop;
use yii\web\JsExpression;

kartik\select2\Select2Asset::register($this);

$this->registerCss('#loader-show {height:100%;}');
$this->registerCss('.wrap > .container {padding:50px  15px 20px !important;}');

$categoryUrl = Url::to(['category', 'vendor_id' => $vendor->id, 'id' => '']);
$clearUrl = Url::to(['ajax-clear-category']);
$setUrl = Url::to(['ajax-set-category']);

$clearUrlMulti = Url::to(['ajax-clear-category-multi']);
$setUrlMulti = Url::to(['ajax-set-category-multi']);

$customJs = <<< JS
        
        $(document).on("click", ".clear-category", function() {
            $("#loader-show").showLoading();
            $.post(
                "$clearUrl",
                {"id": $(this).data("id")}
            ).done(function(result) {
                if (result) {
                    $.pjax.reload({container: "#categories"});
                }
                $("#loader-show").hideLoading();
            })
            .fail(function() {
                        $("#loader-show").hideLoading();
                   });
        });

        $(document).on("click", "#clear-multi", function() {
            if($("#tb-left").yiiGridView("getSelectedRows").length == 0) 
                  {
                    alert('Не выбраны товары!');
                    return;
                }
            
            $("#loader-show").showLoading();
            $.post(
                       "$clearUrlMulti",
                       {"pk" : $("#tb-left").yiiGridView("getSelectedRows")},
                   )
                   .done(function(result) {
                        if (result) {
                            $.pjax.reload({container: "#categories"});
                        }
                        $("#loader-show").hideLoading();
                    })
                   .fail(function() {
                        $("#loader-show").hideLoading();
                    });
        });
        
        $(document).on("click", ".set-category", function() {
            if(!$("#subcat").val())
                {
                    alert('Не выбрана категория или подкатегория!');
                    return;
                }
            $("#loader-show").showLoading();
            $.post(
                "$setUrl",
                {"id": $(this).data("id"), "category_id": $(this).data("category")}
            ).done(function(result) {
                if (result) {
                    $.pjax.reload({container: "#categories"});
                }
                $("#loader-show").hideLoading();
            })
            .fail(function() {
                        $("#loader-show").hideLoading();
                   });
        });
        
        $(document).on("click", "#set-multi", function() {
          
             if(!$("#subcat").val())
                {
                    alert('Не выбрана категория или подкатегория!');
                    return;
                }

             if($("#tb-right").yiiGridView("getSelectedRows").length == 0) 
                  {
                    alert('Не выбраны товары!');
                    return;
                }
                
            $("#loader-show").showLoading();
            $.post(
                        "$setUrlMulti",
                        {
                          "category_id": $("#subcat").val(),
                          "pk" : $("#tb-right").yiiGridView("getSelectedRows")
                        }
                   )
                   .done(function(result) {
                        if (result) {
                            $.pjax.reload({container: "#categories"});
                        }
                        $("#loader-show").hideLoading();
                    })
                   .fail(function() {
                        $("#loader-show").hideLoading();
                   });
        });
        
        $('#subcat').on('change', function(event) {
            $("#loader-show").showLoading();
            $.pjax({
            type: 'GET',
            push: true,
            timeout: 10000,
            url: "$categoryUrl" + $("#subcat").val(),
            container: '#categories',
          }).
          done(function(){
                $("#loader-show").hideLoading();
            })
          .fail(function() {
                        $("#loader-show").hideLoading();
                   });
        });
        
        $(document).on('pjax:send', function() {
          $("#loader-show").showLoading();
        });
        $(document).on('pjax:complete', function() {
              $("#loader-show").hideLoading();  
        });
JS;
$this->registerJs($customJs, View::POS_READY);

$dataProviderCategory->pagination->pageParam = 'category-page';
$dataProviderCategory->sort->sortParam = 'category-sort';

$dataProviderEmpty->pagination->pageParam = 'empty-page';
$dataProviderEmpty->sort->sortParam = 'empty-sort';

?>
<h2><?= $vendor->name ?></h2>
<?= Html::a("<i class=\"fa fa-fw fa-arrow-left\"></i> Назад", ['goods/vendor', 'id' => $vendor->id], ['class' => 'btn btn-success']) ?>
<div class="row">
    <div class="col-md-12" id="b-category"
         style="border: 1px dashed #77c497; padding: 15px;margin-top: 20px;margin-bottom: 10px">
        <label class="control-label" for="parentCategory">Категория товара</label>
        <?php
        echo kartik\select2\Select2::widget([
            'name' => 'sub1',
            'value' => $category,
            'data' => ArrayHelper::map(\common\models\MpCategory::find()->where('parent IS NULL')->asArray()->all(), 'id', 'name'),
            'options' => ['placeholder' => 'Выберите...', 'id' => 'parentCategory'],
            'theme' => "default",
            //'hideSearch' => true,
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        echo Html::hiddenInput('parent', null, ['id' => 'parent_id']);
        echo Html::hiddenInput('child', null, ['id' => 'child_id']);
        ?>
        <?php
        echo kartik\widgets\DepDrop::widget([
            'options' => ['value' => $subCategory->id,],
            'name' => 'sub2',
            //'value' => $subCategory->id,
            'id' => 'subcat',
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => [
                'theme' => "default",
                //'hideSearch' => true,
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],
            'data' => [$subCategory->id => $subCategory->name],
            'pluginOptions' => [
                'depends' => ['parentCategory'],
                //'placeholder' => false,
                'placeholder' => 'Выберите...',
                'url' => Url::to(['get-sub-cat']),
                'loadingText' => 'Загрузка...',
                'initialize' => true,
                //'initDepends'=>['dynamicmodel-sub2'],
                'params' => ['parent_id', 'child_id'],
            ],

        ]);
        ?>
    </div>
    <?php Pjax::begin(['id' => 'categories', 'timeout' => 5000]); ?>
    <div class="col-md-6">
        <?= 'Категория: ' . $category->name . '>' . $subCategory->name ?>
        <?=
        GridView::widget([
            'id' => 'tb-left',
            'dataProvider' => $dataProviderCategory,
            'filterModel' => $searchSetModel,
            'summary' => '',
            'columns' => [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    // you may configure additional properties here
                    'contentOptions'=>['style'=>'width: 30px;'],
                ],
                ['attribute' => 'article',
                    'filter' => false
                ],
                'product',
                [
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::buttonInput(">", ['class' => 'clear-category', 'data-id' => $data['id']]);
                    },
                    'contentOptions'=>['style'=>'width: 40px;'],
                ],
            ],
        ]);
        ?>
        <?= Html::Button(
            "Убрать выделенные позиции из категории <i class=\"fa fa-fw fa-chevron-right\"></i>",
            [
                'id' => 'clear-multi',
                'class' => 'btn btn-danger btn-sm',
            ]
        ); ?>
    </div>
    <div class="col-md-6">
        Без категории
        <?=
        GridView::widget([
            'id' => 'tb-right',
            'dataProvider' => $dataProviderEmpty,
            'filterModel' => $searchModel,
            'summary' => '',
            'columns' => [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    // you may configure additional properties here
                    'contentOptions'=>['style'=>'width: 30px;'],
                ],
                [
                    'format' => 'raw',
                    'value' => function ($data) use ($subCategory) {
                        return Html::buttonInput("<", ['class' => 'set-category', 'data-id' => $data['id'], 'data-category' => $subCategory->id]);
                    },
                    'contentOptions'=>['style'=>'width: 40px;'],

                ],
                ['attribute' => 'article',
                    'filter' => false
                ],
                'product',
            ],
        ]);
        ?>
        <?= Html::Button(
            "<i class=\"fa fa-fw fa-chevron-left\"></i> Добавить выделенные позиции в категорию",
            [
                'id' => 'set-multi',
                'class' => 'btn btn-danger btn-sm',
            ]
        ); ?>
    </div>
</div>
<?php Pjax::end(); ?>