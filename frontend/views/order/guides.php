<?php

use yii\helpers\Url;
use yii\widgets\ListView;
use yii\web\View;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

$this->title = Yii::t('message', 'frontend.views.order.guide_list', ['ru' => "Список шаблонов"]);

yii\jui\JuiAsset::register($this);

$guideUrl = Url::to(['order/ajax-create-guide']);
$guideUrlRename = Url::to(['order/ajax-rename-guide']);
$css = <<< CSS
#yii-debug-toolbar{
    display: none !important;
}
CSS;
$this->registerCss($css);

$this->registerJs('
    $(document).on("click", ".delete-guide", function(e) {
        e.preventDefault();
        clicked = $(this);
        title = "' . Yii::t('message', 'frontend.views.order.deleting_guide', ['ru' => 'Удаление шаблона']) . ' ";
        text = "' . Yii::t('message', 'frontend.views.order.del_guide', ['ru' => 'Вы уверены, что хотите удалить шаблон?']) . ' ";
        success = "' . Yii::t('message', 'frontend.views.order.guide_deleted', ['ru' => 'Шаблон удалён!']) . ' ";
        swal({
            title: title,
            text: text,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "' . Yii::t('message', 'frontend.views.order.yep_delete', ['ru' => 'Да, удалить']) . ' ",
            cancelButtonText: "' . Yii::t('message', 'frontend.views.order.cancel_two', ['ru' => 'Отмена']) . ' ",
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return new Promise(function (resolve, reject) {
                    $.post(
                        clicked.data("url")
                    ).done(function (result) {
                        if (result) {
                            resolve(result);
                            $.pjax.reload("#guidesList", {timeout:30000});
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

    $(document).on("click", ".new-guid", function(e) {
        e.preventDefault();
        var clicked = $(this);
        var title = "' . Yii::t('message', 'frontend.views.order.set_name_for_guide', ['ru' => 'Назовите ваш новый шаблон']) . ' ";
        swal({
            title: title,
            input: "text",
            showCancelButton: true,
            cancelButtonText: "' . Yii::t('message', 'frontend.views.order.cancel_three', ['ru' => 'Отмена']) . ' ",
            confirmButtonText: "' . Yii::t('message', 'frontend.views.order.accept', ['ru' => 'Принять']) . ' ",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            onClose: function() {
                clicked.blur();
                swal.resetDefaults()
            },
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        "' . $guideUrl . '?name=" + text
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
                document.location = result.value.url;
            } else if (result.dismiss === "cancel") {
                swal.close();
            } else {
                swal({title: "' . Yii::t('error', 'frontend.views.order.error', ['ru' => 'Ошибка!']) . ' ", text: "' . Yii::t('message', 'frontend.views.order.try_again', ['ru' => 'Попробуйте еще раз']) . ' ", type: "error"});
            }
        });
    });
    
    $(document).on("click", ".add-note", function(e) {
        e.preventDefault();
        var clicked = $(this);
        var title = "' . Yii::t('message', 'frontend.views.order.good_comment', ['ru' => 'Комментарий к товару']) . ' ";
        fixBootstrapModal();
        fixBootstrapModal();
        swal({
            title: title,
            input: "textarea",
            showCancelButton: true,
            cancelButtonText: "' . Yii::t('message', 'frontend.views.order.close', ['ru' => 'Закрыть']) . ' ",
            confirmButtonText: "' . Yii::t('message', 'frontend.views.order.save', ['ru' => 'Сохранить']) . ' ",
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
                swal({title: "' . Yii::t('error', 'frontend.views.order.error_two', ['ru' => 'Ошибка!']) . ' ", text: "' . Yii::t('message', 'frontend.views.order.try_again_two', ['ru' => 'Попробуйте еще раз']) . ' ", type: "error"});
            }
        });
    });

    $(document).on("click", ".add-to-cart", function(e) {
        e.preventDefault();
        var btnAddToCart = $(this);
        if (btnAddToCart.hasClass("disabled")) {
            return false;
        }
        var quantityInput = $(this).parent().parent().find(".quantity");
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
            {"id": $(this).data("id"), "quantity": quantityInput.val(), "cat_id": $(this).data("cat")}
        ).done(function(result) {
        });
        //quantityInput.val(0);
        btnAddToCart.addClass("disabled");
    });

    $(document).on("click", ".add-guide-to-cart", function(e) {
        e.preventDefault();
        var cart = $(".basket_a");
        var imgtodrag = $("#cart-image");
        var form = $("#gridForm");
        var url = $(this).data("url");
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
            url,
            form.serialize()
        ).done(function(result) {
            console.log(result);
            if(result == false){
                $("#guideModal .modal-header").append("<p class=bg-danger>' . Yii::t('message', 'frontend.views.order.guides.quantity', ['ru' => 'Кол-во товаров:']) . ' 0</p>");
            }else{
                $("#guideModal").modal("toggle");
            }
        });
    });
    
    $(document).on("change paste keyup", ".quantity", function() {
        var btnAddToCart = $(this).parent().parent().parent().find(".add-to-cart");
        if ($(this).val() >= 0) {
            btnAddToCart.removeClass("disabled");
        } else {
            btnAddToCart.addClass("disabled");
        }
    });
    
    $(document).on("click", ".btnSubmit", function() {
        $($(this).data("target-form")).submit();
    });

    $(document).on("hidden.bs.modal", "#guideModal", function() {
        $(this).data("bs.modal", null);
        $(".modal-header").html("<span class=\'glyphicon-left glyphicon glyphicon-refresh spinning\'></span>");
        $(".modal-body").html("");
    });
    
    $(document).on("loaded.bs.modal", "#guideModal", function(){
        $(".modal-dialog").removeAttr("style");
    });
    
    $(document).on("click", ".rename-template", function(e) {
        e.preventDefault();
        var clicked = $(this);
        var title = "' . Yii::t('app', 'frontend.views.order.guides.rename', ['ru' => 'Переименовать шаблон']) . '";
        swal({
            title: title,
            input: "text",
            inputValue: clicked.parent().find(".title a").text(),
            showCancelButton: true,
            cancelButtonText: "' . Yii::t('app', 'frontend.views.order.guides.cancel', ['ru' => 'Отмена']) . '",
            confirmButtonText: "' . Yii::t('app', 'frontend.views.order.guides.rename_two', ['ru' => 'Переименовать']) . '",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            onClose: function() {
                clicked.blur();
                swal.resetDefaults()
            },
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        "' . $guideUrlRename . '", {"name": text, "id": clicked.data("id")}
                    ).done(function (result) {
                        if (result) {
                            clicked.parent().find(".title a").html(text);
                            resolve(result);
                        } else {
                            resolve(false);
                        }
                    });
                })
            },
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else if (result.value.type !== "success") {
               swal({title: "' . Yii::t('error', 'frontend.views.order.error_two', ['ru' => 'Ошибка!']) . '", text: "' . Yii::t('message', 'frontend.views.order.try_again_two', ['ru' => 'Попробуйте ещё раз']) . '", type: "error"});
            }
        });
    });

', View::POS_READY);
?>
<img id="cart-image" src="/images/cart.png" style="position:absolute;left:-100%;">
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li>
                <a href="<?= Url::to(['order/create']) ?>"><?= Yii::t('message', 'frontend.views.order.all_goods', ['ru' => 'Все продукты']) ?></a>
            </li>
            <li class="active">
                <a href="#">
                    <?= Yii::t('message', 'frontend.views.order.orders_guides', ['ru' => 'Шаблоны заказов']) ?>
                    <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="<?= Url::to(['order/favorites']) ?>">
                    <?= Yii::t('message', 'frontend.views.order.faq', ['ru' => 'Часто заказываемые товары']) ?>
                    <small class="label bg-yellow">new</small>
                </a>
            </li>
            <?php
            $disabled_roles = [
                \common\models\Role::ROLE_RESTAURANT_BUYER,
                \common\models\Role::ROLE_RESTAURANT_JUNIOR_BUYER,
                \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR,
            ];
            if ($client->parent_id == null && !in_array(Yii::$app->user->identity->role_id, $disabled_roles)) : ?>
                <li>
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
                        <div class="pull-left">
                            <?php
                            $form = ActiveForm::begin([
                                'method'  => 'get',
                                'options' => [
                                    'id'    => 'searchForm',
                                    'class' => "navbar-form no-padding no-margin",
                                    'role'  => 'search',
                                ],
                            ]);
                            ?>
                            <?=
                            $form->field($searchModel, 'searchString', [
                                'addon'   => [
                                    'append' => [
                                        'content' => '<a class="btn-xs btnSubmit" data-target-form="#searchForm"><i class="fa fa-search"></i></a>',
                                        'options' => [
                                            'class' => 'append',
                                        ],
                                    ],
                                ],
                                'options' => [
                                    'class' => "margin-right-15 form-group",
                                ],
                            ])
                                ->textInput([
                                    'id'          => 'searchString',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('message', 'frontend.views.order.search', ['ru' => 'Поиск'])])
                                ->label(false)
                            ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                        <?php if (Yii::$app->user->identity->role_id != \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR): ?>
                            <div class="pull-right">
                                <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('message', 'frontend.views.order.create_template', ['ru' => 'Создать шаблон']), '#', ['class' => 'btn btn-md btn-outline-success new-guid']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 guid">
                    <?php
                    Pjax::begin(['formSelector' => '#searchForm', 'enablePushState' => false, 'id' => 'guidesList', 'timeout' => 30000]);
                    ?>
                    <?=
                    ListView::widget([
                        'dataProvider' => $dataProvider,
                        'itemView'     => 'guides/_guide-view',
                        'itemOptions'  => [
                            'tag'   => 'div',
                            'class' => 'guid_block',
                        ],
                        'pager'        => [
                            'maxButtonCount' => 5,
                            'options'        => [
                                'class' => 'pagination col-md-12  no-padding'
                            ],
                        ],
                        'options'      => [
                            'class' => 'col-lg-12 list-wrapper inline no-padding'
                        ],
                        'layout'       => "\n{items}\n<div class='pull-left'>{pager}</div><div class='pull-right summary-pages'>{summary}</div>",
                        'summary'      => '',
                        'emptyText'    => Yii::t('message', 'frontend.views.order.empty_list', ['ru' => 'Список пуст']),
                    ])
                    ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?=
Modal::widget([
    'id'            => 'guideModal',
    'clientOptions' => false,
    'size'          => Modal::SIZE_LARGE,
    'header'        => '<span class=\'glyphicon-left glyphicon glyphicon-refresh spinning\'></span>',
])
?>
