<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div id="<?= $id ?>" class="modal fade data-modal">
    <div class="modal-dialog">
        <button type="button" data-dismiss="modal" class="close hidden"></button>
        <div class="modal-content modal-content-info data-modal-info">
            <div class="first-step">
                <div class="data-modal__logo"><img src="<?= $baseUrl ?>/images/logo.png" alt=""></div>
                <div class="data-modal__sub-txt"><?= Yii::t('message', 'frontend.views.client.dashboard.sorry', ['ru'=>'Простите за неудобства, но для корректной работы в системе<br>нам требуется получить от Вас еще несколько данных.']) ?></div>
                <?php
                $form = ActiveForm::begin([
                            'id' => 'complete-form',
                            'enableAjaxValidation' => true,
                            'enableClientValidation' => false,
                            'validateOnSubmit' => true,
                            'action' => $action,
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
        </div>
    </div>
</div>