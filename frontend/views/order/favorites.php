<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\widgets\TouchSpin;

$this->title = "Часто заказываемые товары";

yii\jui\JuiAsset::register($this);

$this->registerJs('
    $(document).on("click", ".add-note", function(e) {
        e.preventDefault();
        var clicked = $(this);
        var title = "Комментарий к товару";
        fixBootstrapModal();
        swal({
            title: title,
            input: "textarea",
            showCancelButton: true,
            cancelButtonText: "Закрыть",
            confirmButtonText: "Сохранить",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            inputValue: clicked.data("original-title"),
            onClose: function() {
                clicked.blur();
                swal.resetDefaults();
                restoreBootstrapModal();
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
            if (result.type == "success") {
                clicked.tooltip("hide")
                    .attr("data-original-title", result.comment)
                    .tooltip("fixTitle")
                    .blur();
                clicked.data("original-title", result.comment);
                swal(result);
            } else {
                swal({title: "Ошибка!", text: "Попробуйте еще раз", type: "error"});
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
<section class="content circe_font">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>">Все продукты</a></li>
            <li>
                <a href="<?= Url::to(['order/guides']) ?>">
                    Гайды заказов <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li class="active">
                <a href="#">
                    Избранные <small class="label bg-yellow">new</small>
                </a>
            </li>
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
                    'placeholder' => 'Поиск по названию'])
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
                            return "<div class='guid_block_create_title'><p>" . $data->product . "</p></div>"
                                    . "<div class='guid_block_create_counts'><p>" . $data->vendor->name . "</p></div>";
                        },
                        'contentOptions' => ['style' => 'width: 40%;'],
                    ],
                    ['format' => 'raw',
                        'attribute' => 'price',
                        'value' => function($data) {
                            return $data->price . ' РУБ/' . $data->ed;
                        },
                        'contentOptions' => ['style' => 'width: 20%;'],
                    ],
                    [
                        'attribute' => 'quantity',
                        'content' => function($data) {
                            $units = $data->units;
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
                        'contentOptions' => ['style' => 'width: 20%;'],
                    ],
                    [
                        'format' => 'raw',
                        'value' => function($data) use ($client) {
                            return Html::button('<i class="fa fa-comment"> Комментарий</i>', [
                                        'class' => 'add-note btn btn-md btn-gray pull-right',
                                        'data' => [
                                            'id' => $data->id,
                                            'url' => Url::to(['order/ajax-set-note', 'product_id' => $data->id]),
                                            'toggle' => "tooltip",
                                            'placement' => "bottom",
                                            'original-title' => $data->getClientNote($client->id),
                                        ],
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 5%;'],
                    ],
                    [
                        'format' => 'raw',
                        'value' => function ($data) {
                            return Html::button('<i class="fa fa-shopping-cart"> В корзину</i>', [
                                        'class' => 'add-to-cart btn btn-md btn-success pull-right',
                                        'data-id' => $data->id,
                                        'data-cat' => $data->cat_id,
                                        'title' => 'Добавить в корзину',
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 5%;'],
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