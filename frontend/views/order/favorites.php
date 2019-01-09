<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\widgets\TouchSpin;

$this->title = Yii::t('message', 'frontend.views.order.freq_goods', ['ru'=>"Часто заказываемые товары"]);

yii\jui\JuiAsset::register($this);

$this->registerJs('
    $(document).on("click", ".add-note", function(e) {
        e.preventDefault();
        var clicked = $(this);
        var title = "' . Yii::t('message', 'frontend.views.order.good_comment_two', ['ru'=>'Комментарий к товару']) . ' ";
        swal({
            title: title,
            input: "textarea",
            showCancelButton: true,
            cancelButtonText: "' . Yii::t('message', 'frontend.views.order.close_two', ['ru'=>'Закрыть']) . ' ",
            confirmButtonText: "' . Yii::t('message', 'frontend.views.order.save_two', ['ru'=>'Сохранить']) . ' ",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            inputValue: clicked.data("original-title"),
            onClose: function() {
                clicked.blur();
                swal.resetDefaults();
            },
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        clicked.data("url"),
                        {comment: text}
                    ).done(function (result) {
                        if (result) {
                            resolve(result);
                        } else {
                            resolve(false);
                        }
                    });
                })
            },
        }).then(function (result) {
            if (result.value.type == "success") {
                clicked.tooltip("hide")
                    .attr("data-original-title", result.value.comment)
                    .tooltip("fixTitle")
                    .blur();
                clicked.data("original-title", result.value.comment);
                swal(result.value);
            } else if (result.dismiss === "cancel") {
                swal.close();
            } else {
                swal({title: "' . Yii::t('error', 'frontend.views.order.error_three', ['ru'=>'Ошибка!']) . ' ", text: "' . Yii::t('message', 'frontend.views.order.try_again_three', ['ru'=>'Попробуйте еще раз']) . ' ", type: "error"});
            }
        });
    });

    $(document).on("click", ".add-to-cart", function(e) {
        e.preventDefault();
        quantity = $(this).parent().parent().find(".quantity").val();
        var cart = $(".basket_a");
        var imgtodrag = $("#cart-image");
        if (imgtodrag) {
            var imgclone = imgtodrag.clone()
                .offset({
                top: $(this).offset().top - 30,
                left: $(this).offset().left + 60
            })
                .css({
                "opacity": "0.5",
                    "position": "absolute",
                    "height": "60px",
                    "width": "60px",
                    "z-index": "10000"
            })
                .appendTo($("body"))
                .animate({
                "top": cart.offset().top,
                    "left": cart.offset().left,
                    "width": 60,
                    "height": 60
            }, 1000, "easeInOutExpo");

            setTimeout(function () {
                cart.parent().effect("highlight", {
                    times: 2,
                    color: "#6ea262"
                }, 350);
            }, 1000);

            imgclone.animate({
                "width": 0,
                    "height": 0
            }, function () {
                $(this).detach()
            });
        }
        $.post(
            "' . Url::to(['/order/ajax-add-to-cart']) . '",
            {"id": $(this).data("id"), "quantity": quantity, "cat_id": $(this).data("cat")}
        ).done(function(result) {
        });
    });

    $(document).on("click", ".btnSubmit", function() {
        $($(this).data("target-form")).submit();
    });
', View::POS_READY);
?>
<img id="cart-image" src="/images/cart.png" style="position:absolute;left:-100%;">
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>"><?= Yii::t('app', 'frontend.views.order.favorites.all', ['ru'=>'Все продукты']) ?></a></li>
            <li>
                <a href="<?= Url::to(['order/guides']) ?>">
                    <?= Yii::t('app', 'frontend.views.order.favorites.templates', ['ru'=>'Шаблоны заказов']) ?> <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li class="active">
                <a href="#">
                    <?= Yii::t('app', 'frontend.views.order.favorites.freq', ['ru'=>'Часто заказываемые товары']) ?> <small class="label bg-yellow">new</small>
                </a>
            </li>
            <?php
            $disabled_roles = [
                \common\models\Role::ROLE_RESTAURANT_BUYER,
                \common\models\Role::ROLE_RESTAURANT_JUNIOR_BUYER,
                \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR,
            ];
            if ($client->parent_id == null && !in_array(Yii::$app->user->identity->role_id, $disabled_roles)) : ?>    <li>
                    <a href="<?= Url::to(['order/product-filter']) ?>">
                        <?= Yii::t('message', 'frontend.views.order.filter_product', ['ru' => 'Фильтрация товаров']) ?>
                        <small class="label bg-yellow">new</small>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        <div class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="guid-header">
                        <?php
        $form = ActiveForm::begin([
                    'options' => [
                        'id' => 'searchForm',
                        'role' => 'search',
                    ],
        ]);
        ?>
        <?=
                $form->field($searchModel, 'searchString', [
                    'addon' => [
                        'append' => [
                            'content' => '<a class="btn-xs btnSubmit" data-target-form="#searchForm"><i class="fa fa-search"></i></a>',
                            'options' => [
                                'class' => 'append',
                            ],
                        ],
                    ],
                    'options' => [
                        'class' => "form-group",
                        'style' => "width:300px;",
                    ],
                ])
                ->textInput([
                    'id' => 'searchString',
                    'class' => 'form-control',
                    'placeholder' => Yii::t('app', 'frontend.views.order.favorites.search', ['ru'=>'Поиск по названию'])])
                ->label(false)
        ?>
        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">   
                    <?php
            Pjax::begin(['formSelector' => '#searchForm', 'enablePushState' => false, 'id' => 'favoritesList', 'timeout' => 30000]);
            ?>
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
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
                            return $data["price"] . ' ' . $data["symbol"] . '/' . Yii::t('app', $data["ed"]);
                        },
                        'contentOptions' => ['style' => 'width: 20%;'],
                    ],
                    [
                        'attribute' => 'quantity',
                        'content' => function($data) {
                            $units = $data["units"];
                            return TouchSpin::widget([
                                        'name' => '',
                                        'pluginOptions' => [
                                            'initval' => 0.100,
                                            'min' => (isset($units) && ($units > 0)) ? $units : 0.001,
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
                        'contentOptions' => ['style' => 'width: 10%;'],
                    ],
                    /*[
                        'format' => 'raw',
                        'value' => function($data) use ($client) {
                            return Html::button('<i class="fa fa-comment"> <span class="circe_font"> ' . Yii::t('message', 'frontend.views.order.favorites.comment', ['ru'=>'Комментарий']) . ' </span></i>', [
                                        'class' => 'add-note btn btn-md btn-gray pull-right circe_font',
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
                            return Html::button('<i class="fa fa-shopping-cart"> <span class="circe_font"> ' . Yii::t('message', 'frontend.views.order.favorites.in_basket', ['ru'=>'В корзину']) . '</span></i>', [
                                        'class' => 'add-to-cart btn btn-md btn-success pull-left circe_font',
                                        'data-id' => $data["cbg_id"],
                                        'data-cat' => $data["cat_id"],
                                        'title' => Yii::t('app', 'frontend.views.order.favorites.add_to_basket', ['ru'=>'Добавить в корзину']),
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 20%; '],
                    ],
                ],
            ]);
            ?>
            <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
        <!-- /.tab-content -->
    </div>
</section>
