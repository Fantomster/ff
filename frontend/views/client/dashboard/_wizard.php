<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\frontend\assets\InfoPopupAsset::register($this);
\frontend\assets\GoogleMapsAsset::register($this);

$this->registerJs('
    function stopRKey(evt) { 
        var evt = (evt) ? evt : ((event) ? event : null); 
        var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
        if ((evt.keyCode == 13) && (node.type=="text")) {return false;} 
    } 

    document.onkeypress = stopRKey; 

    $(document).on("click", ".next", function(e) {
        e.preventDefault();
        $(".data-modal .modal-content").slick("slickNext");
    });
    
    $(document).on("click", ".wizard-off", function(e) {
        e.preventDefault();
        var url = $(this).attr("href");
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "'.Url::to(['/site/ajax-wizard-off']).'",
            success: function (response) {
                document.location = url;
            },
            async: false
        });
    });

    $(document).on("submit", "#complete-form", function() {
        var form = $(this);
        $.post(
            form.attr("action"),
            form.serialize()
        ).done(function(result) {
            if (result.length == 0) {
                document.location.reload();
                //$(".data-modal .modal-content").slick("slickNext");
            }
        });
        return false;
    });

    $(document).on("afterValidate", "#complete-form", function(event, messages, errorAttributes) {
        for (var input in messages) {
            if (messages[input] != "") {
                $("#" + input).tooltip({title: messages[input], placement: "auto right", container: "body"});
                $("#" + input).tooltip();
                $("#" + input).tooltip("show");
                return;
            }
        }
    });


    $("#data-modal-wizard").on("shown.bs.modal",function(){
        $(".data-modal .modal-content").slick({arrows:!1,dots:!1,swipe:!1,infinite:!1,adaptiveHeight:!0})
    });
    $("body").on("hidden.bs.modal", "#data-modal-wizard", function() {
        document.location.reload();
    })
//    $("#data-modal-wizard").length>0&&$("#data-modal-wizard").modal({backdrop: "static", keyboard: false});
',yii\web\View::POS_READY);
?>
<div id="data-modal-wizard" class="modal fade data-modal">
    <div class="modal-dialog">
        <button type="button" data-dismiss="modal" class="close hidden"></button>
        <div class="modal-content">
            <div class="first-step">
                <div class="data-modal__logo"><img src="/images/tmp_file/logo.png" alt=""></div>
                <div class="data-modal__sub-txt"><?= Yii::t('message', 'frontend.views.client.dashboard.sorry', ['ru'=>'Простите за неудобства, но для корректной работы в системе<br>нам требуется получить от Вас еще несколько данных.']) ?></div>
                <?php
                $form = ActiveForm::begin([
                            'id' => 'complete-form',
                            'enableAjaxValidation' => true,
                            'enableClientValidation' => false,
                            'validateOnSubmit' => true,
                            'action' => Url::to(['/site/ajax-complete-registration']),
                            'options' => [
                                'class' => 'auth-sidebar__form form-check data',
                            ],
                            'fieldConfig' => ['template' => '{input}'],
                ]);
                ?>
                <?= Html::activeHiddenInput($organization, 'lat'); //широта ?>
                <?= Html::activeHiddenInput($organization, 'lng'); //долгота ?>
                <?= Html::activeHiddenInput($organization, 'country'); //страна ?> 
                <?= Html::activeHiddenInput($organization, 'locality'); //Город ?>
                <?= Html::activeHiddenInput($organization, 'route'); //улица ?>
                <?= Html::activeHiddenInput($organization, 'street_number'); //дом ?>
                <?= Html::activeHiddenInput($organization, 'administrative_area_level_1'); //область ?>
                <?= Html::activeHiddenInput($organization, 'place_id'); //уникальный индификатор места ?>
                <?= Html::activeHiddenInput($organization, 'formatted_address'); //полный адрес ?>
                <div class="auth-sidebar__form-brims">
                    <label>
                        <?=
                                $form->field($profile, 'full_name')
                                ->label(false)
                                ->textInput(['class' => 'form-control-ip', 'placeholder' => Yii::t('message', 'frontend.views.client.dashboard.fio', ['ru'=>'ФИО'])]);
                        ?>
                        <i class="fa fa-user"></i>
                    </label>
                    <label>
                        <?=
                                $form->field($organization, 'name')
                                ->label(false)
                                ->textInput(['class' => 'form-control-ip', 'placeholder' => Yii::t('message', 'frontend.views.client.dashboard.org_name', ['ru'=>'Название организации'])]);
                        ?>
                        <i class="fa fa-bank"></i>
                    </label>
                    <label>
                        <?=
                                $form->field($organization, 'address')
                                ->label(false)
                                ->textInput(['class' => 'form-control-ip', ' onsubmit' => 'return false', 'placeholder' => Yii::t('message', 'frontend.views.client.dashboard.address', ['ru'=>'Адрес'])])
                        ?>
                        <i class="fa fa-map-marker"></i>
                    </label>
                </div>
                <div id="map" class="modal-map"></div>
                <button type="submit" class="but but_grey" data-dismiss="modal"><span><?= Yii::t('message', 'frontend.views.client.settings.cancel_two', ['ru'=>'Отмена']) ?></span><i class="ico"></i></button>
                <button type="submit" class="but but_green complete-reg"><span><?= Yii::t('message', 'frontend.views.client.settings.save_two', ['ru'=>'Сохранить']) ?></span><i class="ico"></i></button>
                <?php ActiveForm::end(); ?>
            </div>
<!--            <div class="second-step">
                <div class="data-modal__icons-wrp">
                    <div class="row">
                        <div class="col-md-6 col-xs-6"><i class="ico ico-delivery"></i></div>
                        <div class="col-md-6 col-xs-6"><i class="ico ico-basket"></i></div>
                    </div>
                </div>
                <div class="data-modal__sub-txt"><?= Yii::t('message', 'frontend.views.client.dashboard.wanna_work', ['ru'=>'Вы хотите работать со своими поставщиками или найти новых?']) ?></div>
                <div class="data-modal__buts-wrp">
                    <a href="#" class="search-new but but_green wt next"><span><?= Yii::t('message', 'frontend.views.client.dashboard.find', ['ru'=>'Найти новых']) ?></span></a>
                    <a href="<?= Url::to(['/client/add-first-vendor']) ?>" class="but but_green wizard-off"><span><?= Yii::t('message', 'frontend.views.client.dashboard.make_own', ['ru'=>'Завести своих поставщиков']) ?></span></a>
                </div>
            </div>
            <div class="third-step">
                <div class="data-modal__icons-wrp">
                    <div class="row">
                        <div class="col-md-6"><i class="ico ico-tel"></i></div>
                        <div class="col-md-6"><i class="ico ico-cart"></i></div>
                    </div>
                </div>
                <div class="data-modal__sub-txt"><?= Yii::t('message', 'frontend.views.client.dashboard.can_create', ['ru'=>'Вы можете создать заявку на конкретный продукт,<br>поставщики сами Вас найдут.<br>Или найти продуктов и поставщиков на MixMarket']) ?></div>
                <div class="data-modal__buts-wrp">
                    <a href="<?= Url::to(['/request/list']) ?>" class="but but_green wt wizard-off"><span><?= Yii::t('message', 'frontend.views.client.dashboard.create', ['ru'=>'Создать заявку']) ?></span></a>
                    <a href="https://market.mixcart.ru" class="but but_green"><span><?= Yii::t('message', 'frontend.views.client.dashboard.search', ['ru'=>'Поиск на MixMarket']) ?></span></a>
                </div>
            </div>-->
        </div>
    </div>
</div>