<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

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
        $.ajax({
            async: false,
            type: "POST",
            url: "'.Url::to('/site/ajax-wizard-off').'"
        });
    });

    $(document).on("submit", "#complete-form", function() {
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
        $(".data-modal .modal-content").slick("slickNext");
    });

    $("#data-modal").on("shown.bs.modal",function(){
        $(".data-modal .modal-content").slick({arrows:!1,dots:!1,swipe:!1,infinite:!1,adaptiveHeight:!0})
    });
    $("#data-modal").length>0&&$("#data-modal").modal({backdrop: "static", keyboard: false});
',yii\web\View::POS_READY);
?>
<div id="data-modal" class="modal fade data-modal">
    <div class="modal-dialog">
        <button type="button" data-dismiss="modal" class="close hidden"></button>
        <div class="modal-content">
            <div class="first-step">
                <div class="data-modal__logo"><img src="images/tmp_file/logo.png" alt=""></div>
                <div class="data-modal__sub-txt">Простите за неудобства, но для корректной работы в системе<br>нам требуется получить от Вас еще несколько данных.</div>
                <?php
                $form = ActiveForm::begin([
                            'id' => 'complete-form',
                            'enableAjaxValidation' => true,
                            'enableClientValidation' => false,
                            'validateOnSubmit' => true,
                            'action' => Url::to('/site/ajax-complete-registration'),
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
                <?= Html::activeHiddenInput($organization, 'place_id'); //уникальный индификатор места ?>
                <?= Html::activeHiddenInput($organization, 'formatted_address'); //полный адрес ?>
                <div class="auth-sidebar__form-brims">
                    <label>
                        <?=
                                $form->field($profile, 'full_name')
                                ->label(false)
                                ->textInput(['class' => 'form-control', 'placeholder' => 'ФИО']);
                        ?>
                        <i class="fa fa-user"></i>
                    </label>
                    <label>
                        <?=
                                $form->field($organization, 'name')
                                ->label(false)
                                ->textInput(['class' => 'form-control', 'placeholder' => 'Название организации']);
                        ?>
                        <i class="fa fa-bank"></i>
                    </label>
                    <label>
                        <?=
                                $form->field($organization, 'address')
                                ->label(false)
                                ->textInput(['class' => 'form-control', ' onsubmit' => 'return false', 'placeholder' => 'Адрес'])
                        ?>
                        <i class="fa fa-map-marker"></i>
                    </label>
                </div>
                <div id="map" class="modal-map"></div>
                <button type="submit" class="but but_green complete-reg"><span>Продолжить работу</span><i class="ico"></i></button>
                <?php ActiveForm::end(); ?>
            </div>
            <div class="second-step">
                <div class="data-modal__icons-wrp">
                    <div class="row">
                        <div class="col-md-6 col-xs-6"><i class="ico ico-delivery"></i></div>
                        <div class="col-md-6 col-xs-6"><i class="ico ico-basket"></i></div>
                    </div>
                </div>
                <div class="data-modal__sub-txt">Вы хотите работать со своими поставщиками или найти новых?</div>
                <div class="data-modal__buts-wrp">
                    <a href="#" class="search-new but but_green wt next"><span>Найти новых</span></a>
                    <a href="<?= Url::to('client/add-first-vendor') ?>" class="but but_green wizard-off"><span>Завести своих поставщиков</span></a>
                </div>
            </div>
            <div class="third-step">
                <div class="data-modal__icons-wrp">
                    <div class="row">
                        <div class="col-md-6"><i class="ico ico-tel"></i></div>
                        <div class="col-md-6"><i class="ico ico-cart"></i></div>
                    </div>
                </div>
                <div class="data-modal__sub-txt">Вы можете создать заявку на конкретный продукт,<br>поставщики сами Вас найдут.<br>Или найти продуктов и поставщиков на f-market</div>
                <div class="data-modal__buts-wrp">
                    <a href="<?= Url::to('request/list') ?>" class="but but_green wt wizard-off"><span>Создать заявку</span></a>
                    <a href="https://market.f-keeper.ru" class="but but_green"><span>Поиск на f-market</span></a>
                </div>
            </div>
        </div>
    </div>
</div>