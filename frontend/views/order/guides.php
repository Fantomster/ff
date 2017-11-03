<?php

use yii\helpers\Url;
use yii\widgets\ListView;
use yii\web\View;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

$this->title = "Список шаблонов";

yii\jui\JuiAsset::register($this);

$guideUrl = Url::to(['order/ajax-create-guide']);

$this->registerJs('
    $(document).on("click", ".delete-guide", function(e) {
        e.preventDefault();
        clicked = $(this);
        title = "Удаление шаблона";
        text = "Вы уверены, что хотите удалить шаблон?";
        success = "Шаблон удален!";
        swal({
            title: title,
            text: text,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Да, удалить",
            cancelButtonText: "Отмена",
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
        }).then(function() {
            swal({title: success, type: "success"});
        });
    });

    $(document).on("click", ".new-guid", function(e) {
        e.preventDefault();
        var clicked = $(this);
        var title = "Назовите ваш новый шаблон";
        swal({
            title: title,
            input: "text",
            showCancelButton: true,
            cancelButtonText: "Отмена",
            confirmButtonText: "Принять",
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
                        "' . $guideUrl . '?name=" + text,
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
                document.location = result.url;
            } else {
                swal({title: "Ошибка!", text: "Попробуйте еще раз", type: "error"});
            }
        });
    });
    
    $(document).on("click", ".add-note", function(e) {
        e.preventDefault();
        var clicked = $(this);
        var title = "Комментарий к товару";
        fixBootstrapModal();
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
        quantityInput.val(0);
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
            $("#guideModal").modal("toggle");
        });
    });
    
    $(document).on("change paste keyup", ".quantity", function() {
        var btnAddToCart = $(this).parent().parent().parent().find(".add-to-cart");
        if ($(this).val() > 0) {
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

', View::POS_READY);
?>
<img id="cart-image" src="/images/cart.png" style="position:absolute;left:-100%;">
<section class="content circe_font">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>">Все продукты</a></li>
            <li class="active">
                <a href="#">
                    Шаблоны заказов <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="<?= Url::to(['order/favorites']) ?>">
                    Часто заказываемые товары <small class="label bg-yellow">new</small>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="guid-header">
                        <div class="pull-left">
                            <?php
                            $form = ActiveForm::begin([
                                        'options' => [
                                            'id' => 'searchForm',
                                            'class' => "navbar-form no-padding no-margin",
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
                                            'class' => "margin-right-15 form-group",
                                        ],
                                    ])
                                    ->textInput([
                                        'id' => 'searchString',
                                        'class' => 'form-control',
                                        'placeholder' => 'Поиск'])
                                    ->label(false)
                            ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                        <div class="pull-right">
                            <?= Html::a('<i class="fa fa-plus"></i> Создать шаблон', '#', ['class' => 'btn btn-md btn-outline-success new-guid']) ?>
                        </div>
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
                        'itemView' => 'guides/_guide-view',
                        'itemOptions' => [
                            'tag' => 'div',
                            'class' => 'guid_block',
                        ],
                        'pager' => [
                            'maxButtonCount' => 5,
                            'options' => [
                                'class' => 'pagination col-md-12  no-padding'
                            ],
                        ],
                        'options' => [
                            'class' => 'col-lg-12 list-wrapper inline no-padding'
                        ],
                        'layout' => "\n{items}\n<div class='pull-left'>{pager}</div><div class='pull-right summary-pages'>{summary}</div>",
                        'summary' => '',
                        'emptyText' => 'Список пуст',
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
    'id' => 'guideModal',
    'clientOptions' => false,
    'size' => Modal::SIZE_LARGE,
])
?>