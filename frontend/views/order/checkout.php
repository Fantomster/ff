<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use yii\widgets\Breadcrumbs;
use kartik\form\ActiveForm;
use common\models\User;
use common\models\Role;

$checkoutUrl = Url::to(['order/checkout']);
$this->registerJs(
    '$("document").ready(function(){
            $(document).on("click", ".remove, .delete, .deleteAll", function(e) {
                e.preventDefault();
                if (!$(".block_wrap_bask_tover").length) {
                    return false;
                }
                clicked = $(this);
                activeCart = $(document).find(".block_wrap_bask_tover.active").attr("id");
                if (clicked.hasClass("remove")) {
                    title = "' . Yii::t('message', 'frontend.views.order.del', ['ru' => 'Удаление товара из корзины']) . ' ";
                    text = "' . Yii::t('message', 'frontend.views.order.sure', ['ru' => 'Вы уверены, что хотите удалить товар из заказа?']) . ' ";
                    success = "' . Yii::t('message', 'frontend.views.order.good_deleted', ['ru' => 'Товар удалён!']) . ' ";
                } else if (clicked.hasClass("delete")){
                    title = "' . Yii::t('message', 'frontend.views.order.order_del', ['ru' => 'Удаление заказа']) . ' ";
                    text = "' . Yii::t('message', 'frontend.views.order.sure_two', ['ru' => 'Вы уверены, что хотите удалить заказ из корзины?']) . ' ";
                    success = "' . Yii::t('message', 'frontend.views.order.order_deleted', ['ru' => 'Заказ удалён!']) . ' ";
                } else if (clicked.hasClass("deleteAll")){
                    title = "' . Yii::t('message', 'frontend.views.order.clean_basket', ['ru' => 'Очистка корзины']) . ' ";
                    text = "' . Yii::t('message', 'frontend.views.order.sure_del', ['ru' => 'Вы уверены, что хотите удалить все заказы из корзины?']) . ' ";
                    success = "' . Yii::t('message', 'frontend.views.order.basket_empty', ['ru' => 'Корзина очищена!']) . ' ";
                    dataEdited = 0;
                }
                swal({
                    title: title,
                    text: text,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "' . Yii::t('message', 'frontend.views.order.yep_delete_two', ['ru' => 'Да, удалить']) . ' ",
                    cancelButtonText: "' . Yii::t('message', 'frontend.views.order.cancel_four', ['ru' => 'Отмена']) . ' ",
                    showLoaderOnConfirm: true,
                    preConfirm: function () {
                        return new Promise(function (resolve, reject) {
                            $.post(
                                clicked.data("url")
                            ).done(function (result) {
                                if (result) {
                                    resolve(result);
                                    $.pjax.reload("#checkout", {timeout:30000});
                                } else {
                                    resolve(false);
                                }
                            });
                        })
                    },
                }).then(function(result) {
                    if (result.dismiss === "cancel") {
                        swal.close();
                    } else {
                        swal({title: success, type: "success"});
                    }
                });
            });

            $(document).on("click", ".create, .createAll", function(e) {
                e.preventDefault();
                if (!$(".block_wrap_bask_tover").length) {
                    return false;
                }
                
                var clicked = $(this);
                var form = $("#cartForm");
                var extData = "&all=" + clicked.data("all") + "&id=" + clicked.data("id"); 
                
                if (clicked.hasClass("create")) {
                    var vendor_id = clicked.data("id")
                
                    title = "' . Yii::t('message', 'frontend.views.order.order_create_two', ['ru' => 'Создание заказа']) . ' ";
                    text = "' . Yii::t('message', 'frontend.views.order.will_be_send', ['ru' => 'Заказ будет оформлен и направлен поставщику. Продолжить?']) . ' ";
                } else if (clicked.hasClass("createAll")){
                    title = "' . Yii::t('message', 'frontend.views.order.orders_creating', ['ru' => 'Создание заказов']) . ' ";
                    text = "' . Yii::t('message', 'frontend.views.order.all_goods_three', ['ru' => 'Все заказы из корзины будут оформлены и направлены соответствующим поставщикам. Продолжить?']) . ' ";
                }
                swal({
                    title: title,
                    html:text,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "' . Yii::t('message', 'frontend.views.order.yep_two', ['ru' => 'Да']) . ' ",
                    cancelButtonText: "' . Yii::t('message', 'frontend.views.order.cancel_five', ['ru' => 'Отмена']) . ' ",
                    showLoaderOnConfirm: true,
                    preConfirm: function () {
                        return new Promise(function (resolve, reject) {
                            $.post(
                                clicked.data("url"),
                                form.serialize() + extData
                            ).done(function (result) {
                                if (result) {
                                    resolve(result);
                                     $.pjax.reload("#checkout", {timeout:30000});
                                } else {
                                    resolve(false);
                                }
                            });
                        })
                    },
                }).then(function(result) {
                    if (result.dismiss === "cancel") {
                        swal.close();
                    } else {
                        swal({title: result.value.title, html: result.value.description, type: result.value.type});
                    }
                });
            });

            $(document).on("click", "#saveChanges", function(e) {
                e.preventDefault();
                var clicked = $(this);
                var form = $("#cartForm");
                var extData = "&action=save"; 
                swal({
                    title: "' . Yii::t('message', 'frontend.views.order.saving_changes', ['ru' => 'Сохранение изменений']) . ' ",
                    text: "' . Yii::t('message', 'frontend.views.order.save_three', ['ru' => 'Сохранить изменения в заказах?']) . ' ",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "' . Yii::t('message', 'frontend.views.order.yep_three', ['ru' => 'Да']) . ' ",
                    cancelButtonText: "' . Yii::t('message', 'frontend.views.order.', ['ru' => 'Отмена']) . ' ",
                    showLoaderOnConfirm: true,
                    preConfirm: function () {
                        return new Promise(function (resolve, reject) {
                            $.post(
                                form.attr("action"),
                                form.serialize() + extData
                            ).done(function (result) {
                                if (result) {
                                    $.pjax.reload("#checkout", {url:"' . $checkoutUrl . '",timeout:30000});
                                    dataEdited = 0;
                                    resolve(result);
                                } else {
                                    resolve(false);
                                }
                            });
                        })
                    },
                }).then(function(result) {
                    if (result.dismiss === "cancel") {
                        swal.close();
                    } else {
                        swal(result.value);
                    }
                });
            });
            $("#checkout").on("change", ".delivery-date", function(e) {
                $.post(
                    "' . Url::to(['/order/ajax-set-delivery']) . '",
                    {"vendor_id":$(this).data("vendor_id"), "delivery_date":$(this).val() }
                ).done(function(result) {
                    if (result) {
                        swal(result.value);
                    }
                });
            });

            $(document).on("click", ".comment, .add-note", function(e) {
                e.preventDefault();
                var clicked = $(this);
                if (clicked.hasClass("comment")) {
                    title = "' . Yii::t('message', 'frontend.views.order.order_comment', ['ru' => 'Комментарий к заказу']) . ' ";
                } else {
                    title = "' . Yii::t('message', 'frontend.views.order.product_comment', ['ru' => 'Комментарий к товару']) . ' ";
                }
                swal({
                    title: title,
                    input: "textarea",
                    showCancelButton: true,
                    cancelButtonText: "' . Yii::t('message', 'frontend.views.order.close_three', ['ru' => 'Закрыть']) . ' ",
                    confirmButtonText: "' . Yii::t('message', 'frontend.views.order.save_four', ['ru' => 'Сохранить']) . ' ",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    showLoaderOnConfirm: true,
                    inputValue: clicked.data("original-title"),
                    onClose: function() {
                        clicked.blur();
                        swal.resetDefaults()
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
                        swal({title: "' . Yii::t('error', 'frontend.views.order.error_four', ['ru' => 'Ошибка!']) . ' ", text: "' . Yii::t('message', 'frontend.views.order.try_again_four', ['ru' => 'Попробуйте еще раз']) . ' ", type: "error"});
                    }
                });
            });
            
            $(document).on("change keyup paste cut", ".quantity", function() {
                dataEdited = 1;
                $("#saveChanges").show();
            });
            
            $(document).on("click", ".changed", function() {
                document.location = link;
            });
            
            $(document).on("click", "a", function(e) {
                if ($(".block_wrap_bask_tover").length == 0) {
                    dataEdited = 0;
                }
                if (dataEdited) {
                    e.preventDefault();
                    var link = $(this).attr("href");
                    if ($(this).data("internal") != 1) {
                        if (link != "#") {
                            swal({
                                title: "' . Yii::t('message', 'frontend.views.order.unsaved_changes', ['ru' => 'Несохранённые изменения!']) . ' ",
                                text: "' . Yii::t('message', 'frontend.views.order.not_saved_changes', ['ru' => 'Вы изменили заказ, но не сохранили изменения!']) . ' ",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonText: "' . Yii::t('message', 'frontend.views.order.out', ['ru' => 'Уйти']) . ' ",
                                cancelButtonText: "' . Yii::t('message', 'frontend.views.order.stay', ['ru' => 'Остаться']) . ' ",
                            }).then(function(result) {
                                if (result.dismiss === "cancel") {
                                    swal.close()
                                } else {
                                    document.location = link;
                                }
                            });
                        }
                    }
                }
            });

            $(document).on("click", ".block_wrap_activess,.active_tov", function() { 
                var block = $(this).parent().parent().parent();
                block.toggleClass("active");
            });

            $(document).on("change", ".quantity", function(e) {
                var value = $(this).val();
                $(this).val(value.replace(",", "."));
                var url = $(this).closest(".block_wrap_bask_tover").find(".block_right").data("url");
                var form = $("#cartForm");
                var block = $(this).closest(".block_wrap_bask_tover").find(".block_right");
                var button = $(this).closest(".block_wrap_bask_tover").find(".checkout-button");
                $.post(
                    url,
                    form.serialize()
                ).done(function (result) {
                    block.html(result.total);
                    button.html(result.button);
                    var i;
                    for (i = 0; i < result.expectedPositions.length; ++i) {
                        $("#position_" + result.expectedPositions[i].id).html(result.expectedPositions[i].price);
                    }
                });
            });
            
            $(document).on("change", ".quantity", function(e) {
                
            });

        });'
);
$this->registerCss('
    .date {
        float: right;
        margin-top: 5px;
        margin-right: 10px;
    }
    .delivery-date {
        height: 40px;
        width: 140px !important;
    }
    .alButton{
        background-color: #f4f4f4 !important;
        color: #444 !important;
        border-color: #ddd !important;
    }
    .alRightBlock{
         padding: 5px; background: none; color: black; border: 0; width: 230px;
    }
    .alRightBlock p, .alP{
         padding-top: 8px;
    }
        ');
$this->title = Yii::t('message', 'frontend.views.order.basket', ['ru' => "Корзина"]);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-shopping-cart"></i> <?= Yii::t('message', 'frontend.views.order.basket_two', ['ru' => 'Корзина']) ?>
        <small><?= Yii::t('message', 'frontend.views.order.orders_list_two', ['ru' => 'Список готовящихся заказов']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options'  => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links'    => [
            [
                'label' => Yii::t('message', 'frontend.views.order.set_order_two', ['ru' => 'Разместить заказ']),
                'url'   => ['order/create'],
            ],
            Yii::t('message', 'frontend.views.order.basket_three', ['ru' => 'Корзина']),
        ],
    ])
    ?>
</section>
<?php
Pjax::begin(['enablePushState' => false, 'id' => 'checkout', 'timeout' => 30000]);
?>
<section class="content">
    <div class="box box-info">
        <div class="box-header checkout-header">
            <div class="row">
                <div class="col-md-6 col-sm-8 col-xs-6">
                    <div class="btn-group" role="group" id="createAll">
                        <?php
                        $user_id = Yii::$app->user->id;
                        $role_id = User::find()->select('role_id')->where(['id' => $user_id])->column();
                        if ($role_id[0] != Role::ROLE_RESTAURANT_ORDER_INITIATOR) {
                            echo Html::button('<i class="fa fa-paper-plane" style="margin-top:-3px;"></i><span class="hidden-xs"> ' . Yii::t('message', 'frontend.views.order.make_all', ['ru' => 'Оформить все заказы']) . ' </span>', [
                                'class' => 'btn btn-success createAll',
                                'data'  => [
                                    'url' => Url::to(['/order/ajax-make-order']),
                                    'all' => true,
                                    'id'  => null,
                                ]
                            ]);
                        }
                        ?>
                        <?=
                        ''
                        //                        Html::button("&nbsp;<span>$totalCart</span> <i class='fa fa-fw fa-rub'></i>&nbsp;", [
                        //                            'class' => 'btn btn-success createAll btn-outline total-cart',
                        //                            'data' => [
                        //                                'url' => Url::to(['/order/ajax-make-order']),
                        //                                'all' => true,
                        //                                'id' => null,
                        //                            ]
                        //                        ]);
                        ?>
                    </div>
                </div>
                <div class="col-md-6 col-sm-4 col-xs-6">
                    <?=
                    Html::a('<i class="fa fa-ban" style="margin-top:-3px;"></i><span class="hidden-sm hidden-xs"> ' . Yii::t('message', 'frontend.views.order.basket_empty_two', ['ru' => 'Очистить корзину']) . ' </span>', '#', [
                        'class'    => 'btn btn-danger pull-right deleteAll',
                        'style'    => 'margin-right: 10px; margin-left: 3px;',
                        'data-url' => Url::to(['/order/ajax-delete-order']),
                    ]);
                    ?>
                    <button class="btn btn-success pull-right" style="display:none;" id="saveChanges"><i
                                class="fa fa-save" style="margin-top:-3px;"></i><span
                                class="hidden-sm hidden-xs"> <?= Yii::t('app', 'Сохранить') ?></span></button>
                </div>
            </div>
        </div>
        <div class="box-body">
            <div class="checkout">
                <?php
                $form = ActiveForm::begin([
                    'id'                   => 'cartForm',
                    'enableAjaxValidation' => false,
                    'options'              => [
                        'data-pjax' => true,
                    ],
                    'method'               => 'post',
                    'action'               => Url::to(['order/checkout']),
                ]);
                ?>
                <?php
                foreach ($carts as $cart) {
                    if ($cart['for_min_cart_price']):
                        ?>
                        <style>#createAll {
                                display: none;
                            }</style>
                    <?php endif; ?>
                    <div class="block_wrap_bask_tover" id="cartOrder<?= $cart['id'] ?>">
                        <div class="block_left">
                            <div class="block_left_top">

                                <?=
                                Html::a('<img class= "delete_tovar_bask" src="/img/bask_del.png" alt="">', '#', [
                                    'class'    => 'delete',
                                    'data-url' => Url::to(['/order/ajax-delete-order', 'vendor_id' => $cart['id']]),
                                ]);
                                ?>
                                <div class="block_wrap_activess">
                                    <p class="basket_tovar_postav_name"><?= Yii::t('message', 'frontend.views.order.vendors_order', ['ru' => 'Заказ у поставщика']) ?>
                                        <span><?= $cart['vendor']['name'] ?> </span>
                                        <img class="" src="/img/bot_ar.png" alt="">
                                    </p>
                                </div>
                                <div class="checkout_buttons">
                                    <span class="checkout-button">
                                    <?php
                                    $user_id = Yii::$app->user->id;
                                    $role_id = User::find()->select('role_id')->where(['id' => $user_id])->column();
                                    if ($role_id[0] != Role::ROLE_RESTAURANT_ORDER_INITIATOR) {
                                        echo $this->render("_checkout-position-button", compact("cart"));
                                    }
                                    ?>
                                    </span>
                                    <?=
                                    Html::button(Yii::t('message', 'frontend.views.order.order_comment_two', ['ru' => 'Комментарий к заказу']), [
                                        'class' => 'but_comments comment pull-right',
                                        'data'  => [
                                            'url'            => Url::to(['order/ajax-set-comment', 'vendor_id' => $cart['id']]),
                                            'toggle'         => "tooltip",
                                            'placement'      => "bottom",
                                            "original-title" => Yii::$app->request->cookies->getValue('order_comment_' . $cart['id'], null),
                                        ]
                                    ]);
                                    ?>
                                    <div class="pull-right">
                                        <?php
                                        $lang = (Yii::$app->language == 'md') ? 'ro' : Yii::$app->language;
                                        $delivery_date = Yii::$app->request->cookies->getValue('requested_delivery_' . $cart['id']);
                                        echo DatePicker::widget([
                                            'name'          => '',
                                            'value'         => isset($delivery_date) ? date('d.m.Y', strtotime($delivery_date)) : null,
                                            'options'       => [
                                                'placeholder'    => Yii::t('message', 'frontend.views.order.delivery_date', ['ru' => 'Дата доставки']),
                                                'class'          => 'delivery-date',
                                                'data-vendor_id' => $cart['id'],
                                            ],
                                            'type'          => DatePicker::TYPE_COMPONENT_APPEND,
                                            'layout'        => '{picker}{input}{remove}',
                                            'language'      => $lang,
                                            'pluginOptions' => [
                                                'daysOfWeekDisabled' => $cart['vendor']['disabled_delivery_days'],
                                                'format'             => 'dd.mm.yyyy',
                                                'autoclose'          => true,
                                                'startDate'          => "0d",
                                                'endDate'            => date('d.m.Y', strtotime(date("Y-m-d", mktime()) . " + 365 day")),
                                                'todayHighlight'     => true,
                                            ]
                                        ])
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?= $this->render('_checkout-content', ['cart' => $cart]) ?>
                        </div>
                        <div class="block_right"
                             data-url="<?= Url::to(['order/ajax-calculate-total', 'id' => $cart['id']]) ?>">
                            <?= $this->render("_checkout-total", compact('cart')); ?>
                        </div>
                    </div>
                <?php } ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</section>
<?php Pjax::end() ?>
