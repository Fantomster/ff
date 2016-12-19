<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\web\View;
use yii\helpers\Url;

$clearUrl = Url::to(['ajax-clear-category']);
$setUrl = Url::to(['ajax-set-category']);

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
            });
        });
        
        $(document).on("click", ".set-category", function() {
            $("#loader-show").showLoading();
            $.post(
                "$setUrl",
                {"id": $(this).data("id"), "category_id": $(this).data("category")}
            ).done(function(result) {
                if (result) {
                    $.pjax.reload({container: "#categories"});
                }
                $("#loader-show").hideLoading();
            });
        });
        
JS;
$this->registerJs($customJs, View::POS_READY);

?>
<?php Pjax::begin(['enablePushState' => false, 'id' => 'categories', 'timeout' => 5000]); ?>
<h2><?= $vendor->name ?></h2>
<div class="row">
    <div class="col-md-6">
        <?= 'Категория: ' . $category->name . '>' . $subCategory->name ?>
        <?=
        GridView::widget([
            'dataProvider' => $dataProviderCategory,
            'summary' => '',
            'columns' => [

                'id',
                'article',
                'product',
                [
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::buttonInput(">", ['class' => 'clear-category', 'data-id' => $data['id']]);
                    },
                ],
            ],
        ]);
        ?>
    </div>
    <div class="col-md-6">
        Без категории
        <?=
        GridView::widget([
            'dataProvider' => $dataProviderEmpty,
            'summary' => '',
            'columns' => [
                [
                    'format' => 'raw',
                    'value' => function ($data) use ($subCategory) {
                        return Html::buttonInput("<", ['class' => 'set-category', 'data-id' => $data['id'], 'data-category' => $subCategory->id]);
                    },
                ],
                'id',
                'article',
                'product',
            ],
        ]);
        ?>
    </div>
</div>
<?php Pjax::end(); ?> 