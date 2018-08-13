<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\touchspin\TouchSpin;
use kartik\form\ActiveForm;

$js = <<<SCRIPT
/* To initialize BS3 tooltips set this below */
$(function () { 
    $("[data-toggle='tooltip']").tooltip(); 
});
SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js);

$guideUrl = Url::to(['order/ajax-show-guide', 'id'=>$guide->id]);
$guideSessionUrl = Url::to(['order/ajax-add-good-quantity-to-session']);

$this->registerJs('
    $(document).on("change", ".quantity", function() {
        var quantity = $(this).val();
        var name = $(this).prop("name");
            $.ajax({
             type: "GET",
             url: "' . $guideSessionUrl . '",
             data: {
                    quantity: quantity,
                    name: name
                   }
   }).done(function() { });
    });
    
    
        $(document).on("change", "#guideproductssearch-sort", function() {
        var sort = $(this).val();
            $.pjax({
             type: "GET",
             push: false,
             timeout: 10000,
             url: "' . $guideUrl . '",
             container: "#guideProductList",
             data: {
                    sort: sort,
                   }
   }).done(function() { });
    });
    
    
    $(document).on("change", "#searchString", function() {
        var search = $(this).val();
            $.pjax({
             type: "GET",
             push: false,
             timeout: 10000,
             url: "' . $guideUrl . '",
             container: "#guideProductList",
             data: {
                    search_string: search,
                   }
   }).done(function() {  });
    });
    ', \yii\web\View::POS_READY);
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span></button>
    <h4 class="modal-title"><?= $guide->name ?></h4>
    <span><?= Yii::t('message', 'frontend.views.order.guides.can_manage', ['ru'=>'Вы можете управлять шаблоном в данном заказе']) ?></span>
    <div class="guid-header">
        <?php
        $form = ActiveForm::begin([
                    'options' => [
                        'id' => 'searchProductForm',
                        'role' => 'search',
                        'action' => ['order/ajax-show-guide', 'id' => $guide->id]
                    ],
        ]);
        ?>

        <?=
                $form->field($guideSearchModel, 'searchString', [
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
                    ],
                ])
                ->textInput([
                    'id' => 'searchString',
                    'class' => 'form-control',
                    'placeholder' => Yii::t('message', 'frontend.views.order.guides.search', ['ru'=>'Поиск по названию'])])
                ->label(false)
        ?>

        <?=
        $form->field($guideSearchModel, 'sort', [
            'options' => [
                'id' => 'alSortSelect',
                'class' => "form-group"
            ],
        ])
            ->dropDownList([
                '1' => Yii::t('app', 'frontend.views.guides.sort_by', ['ru' => 'Сортировка по']),
                'product 4' => Yii::t('app', 'frontend.views.guides.sort_by_name_asc', ['ru' => 'Наименованию по возрастанию']),
                'product 3' => Yii::t('app', 'frontend.views.guides.sort_by_name_desc', ['ru' => 'Наименованию по убыванию']),
                'id 4' => Yii::t('app', 'frontend.views.guides.sort_by_time_asc', ['ru' => 'Порядку добавления по возрастанию']),
                'id 3' => Yii::t('app', 'frontend.views.guides.sort_by_time_desc', ['ru' => 'Порядку добавления по убыванию']),
            ], [
                'options' => [$params['sort'] ?? 1 => ['selected' => true], '1' => ['disabled' => true]]])
            ->label(false)
        ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">   
            <?php
            Pjax::begin(['formSelector' => '#searchProductForm', 'enablePushState' => false, 'id' => 'guideProductList', 'timeout' => 30000, 'clientOptions' => ['url' => Url::to(['/order/ajax-show-guide', 'id' => $guide->id])]]);
            ?>
            <?php
            $form = ActiveForm::begin([
                        'options' => [
                            'id' => 'gridForm',
                        ],
            ]);
            ?>
            <?=
            GridView::widget([
                'dataProvider' => $guideDataProvider,
                'filterModel' => $guideSearchModel,
                'filterPosition' => false,
                'summary' => '',
                'showHeader' => false,
                'tableOptions' => ['class' => 'table table-hover'],
                'columns' => [
                    [
                        'format' => 'raw',
                        'attribute' => 'baseProduct.product',
                        'value' => function($data) {
                            return "<div class='guid_block_create_title'><p>" . $data["product"] . "</p></div>"
                                    . "<div class='guid_block_create_counts'><p>" . $data["name"] . "</p></div>";
                        },
                        'contentOptions' => ['style' => 'width: 40%;'],
                    ],
                    ['format' => 'raw',
                        'attribute' => 'price',
                        'value' => function($data) {
                            return '<span data-toggle="tooltip" data-placement="bottom" title="'.Yii::t('message', 'frontend.views.order.price_update', ['ru'=>'Обновлена:']).' '.Yii::$app->formatter->asDatetime($data['price_updated_at'], "dd-MM-YY").'">'.
                                $data["price"] . ' ' . $data["symbol"] . '/' . $data["ed"].'</span>';
                        },
                        'contentOptions' => ['style' => 'width: 20%;'],
                    ],
                    [
                        'attribute' => 'quantity',
                        'content' => function($data) use ($cart){
                            $initVal = $_SESSION['GuideProductCount.' . $data["id"]] ?? 0;
                            foreach($cart as $one){
                                if($data['cbg_id'] == $one['product_id']){
                                    $initVal = $one['quantity'];
                                }
                            }
                            $units = $data['units'] ?? 0.100;
                            return TouchSpin::widget([
                                        'name' => 'GuideProduct[' . $data["id"] . ']',
                                        'pluginOptions' => [
                                            'initval' => $initVal,
                                            'min' => 0,
                                            'max' => PHP_INT_MAX,
                                            'step' => (isset($units) && ($units)) ? $units : 1,
                                            'decimals' => (empty($units) || (fmod($units, 1) > 0)) ? 3 : 0,
                                            'forcestepdivisibility' => (isset($units) && $units && (floor($units) == $units)) ? 'floor' : 'none',
                                            'buttonup_class' => 'btn btn-default',
                                            'buttondown_class' => 'btn btn-default',
                                            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                                        ],
                                        'options' => ['class' => 'quantity form-control '],
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 20%;'],
                    ],
                    /*[
                        'format' => 'raw',
                        'value' => function($data) {
                            return Html::button('<i class="fa fa-comment"> <span class="circe_font">' . Yii::t('message', 'frontend.views.order.guides.comment_two', ['ru'=>'Комментарий']) . ' </span></i>', [
                                        'class' => 'add-note btn btn-md btn-gray pull-right',
                                        'data' => [
                                            'id' => $data["cbg_id"],
                                            'url' => Url::to(['order/ajax-set-note', 'product_id' => $data["cbg_id"]]),
                                            'toggle' => "tooltip",
                                            'placement' => "bottom",
                                            'original-title' => $data["note"],
                                        ],
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 5%;'],
                    ],*/
                    [
                        'format' => 'raw',
                        'value' => function ($data) {
                            if (isset($_SESSION['GuideProductCount.' . $data["id"]]) && $_SESSION['GuideProductCount.' . $data["id"]] > 0 && $_SESSION['GuideProductCount.' . $data["id"]] != '0.000') {
                                $disabled = '';
                            } else {
                                $disabled = ' disabled';
                            }
                            return Html::button('<i class="fa fa-shopping-cart"> <span class="circe_font">' . Yii::t('message', 'frontend.views.order.guides.in_basket_two', ['ru' => 'В корзину']) . ' </span></i>', [
                                'class' => 'add-to-cart btn btn-md btn-success pull-right' . $disabled,
                                'data-id' => $data["cbg_id"],
                                'data-cat' => $data["cat_id"],
                                'title' => Yii::t('message', 'frontend.views.order.guides.add_in_basket', ['ru' => 'Добавить в корзину']),
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 10%;'],
                    ],
                ],
            ]);
            ?>
            <?php ActiveForm::end(); ?>
            <?php Pjax::end(); ?>
            <button style="margin-top: 20px;margin-right: 7px;" class="btn btn-md btn-success pull-right add-guide-to-cart" data-url="<?= Url::to(['order/ajax-add-guide-to-cart', 'id' => $guide->id]) ?>"><i class="fa fa-paper-plane-o"></i> <?= Yii::t('message', 'frontend.views.order.guides.add_all', ['ru'=>'Добавить все']) ?></button>
        </div>
    </div>
</div>
