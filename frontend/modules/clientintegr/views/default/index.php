<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
use kartik\grid\GridView;
use kartik\editable\Editable;
use api\common\models\RkAccess;
use backend\controllers\RkwsController;

?>


<style>
    .bg-default {
        background: #555
    }

    p {
        margin: 0;
    }

    #map {
        width: 100%;
        height: 200px;
    }
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с внешними системами
        <small>Обменивайтесь номенклатурой и приходными документами с Вашей учётной системой автоматически</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            'Интеграция',
        ],
    ])
    ?>
</section>
<?php
$user = Yii::$app->user->identity;
$licenses = $user->organization->getLicenseList();
$timestamp_now = time();

// Блок проверки состояния лицензий R-Keeper

if (isset($licenses['rkws'])) {
    $sub0 = explode(' ', $licenses['rkws']->td);
    $sub1 = explode('-', $sub0[0]);
    $licenses['rkws']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
    if ($licenses['rkws']->status_id == 0) { // если лицензия отключена в админке Mixcart
        $rk_us = 0;
    }
    if (($licenses['rkws']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['rkws']->td)))) { // если лицензия в админке включена, но истёк её срок
        $rk_us = 3;
    }
    if (($licenses['rkws']->status_id == 1) and (($timestamp_now + 14 * 86400) > (strtotime($licenses['rkws']->td)))) { // если лицензия в админке включена, до окончания её срока осталось менее двух недель
        $rk_us = 2;
    }
    if (($licenses['rkws']->status_id == 1) and ($timestamp_now > (strtotime($licenses['rkws']->td)))) { // если лицензия в админке включена и до окончания её срока осталось более двух недель
        $rk_us = 1;
    }
    $sub0 = explode(' ', $licenses['rkws_ucs']->td);
    $sub1 = explode('-', $sub0[0]);
    $licenses['rkws_ucs']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
    $rk_lic = 3;
    if ($licenses['rkws_ucs']->status_id == 0) { // если лицензия отключена в админке Mixcart
        $rk_lic = 0;
    }
   /* if (($licenses['rkws_ucs']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['rkws_ucs']->status_id == 1)))) { // если лицензия учётной системы в админке включена, но истёк её срок
        $rk_lic = 3;
    }
    if (($licenses['rkws_ucs']->status_id == 1) and (($timestamp_now + 14 * 86400) > (strtotime($licenses['rkws_ucs']->td)))) { // если лицензия учётной системы в админке включена, до окончания её срока осталось менее двух недель
        $rk_lic = 2;
    }
    if (($licenses['rkws_ucs']->status_id == 1) and ($timestamp_now > (strtotime($licenses['rkws_ucs']->td)))) { // если лицензия учётной системы в админке включена и до окончания её срока осталось более двух недель
        $rk_lic = 1;
    }*/
}

// Блок проверки состояния лицензии IIKO

if (isset($licenses['iiko'])) {
    $sub0 = explode(' ', $licenses['iiko']->td);
    $sub1 = explode('-', $sub0[0]);
    $licenses['iiko']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
    if ($licenses['iiko']->status_id == 0) { // если лицензия отключена в админке Mixcart
        $lic_iiko = 0;
    }
    if (($licenses['iiko']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['iiko']->td)))) { // если лицензия в админке включена, но истёк её срок
        $lic_iiko = 3;
    }
    if (($licenses['iiko']->status_id == 1) and (($timestamp_now + 14 * 86400) > (strtotime($licenses['iiko']->td)))) { // если лицензия в админке включена, до окончания её срока осталось менее двух недель
        $lic_iiko = 2;
    }
    if (($licenses['iiko']->status_id == 1) and ($timestamp_now > (strtotime($licenses['iiko']->td)))) { // если лицензия в админке включена и до окончания её срока осталось более двух недель
        $lic_iiko = 1;
    }
}

// Блок проверки состояния лицензии Tillypad

if (isset($licenses['tillypad'])) {
    $sub0 = explode(' ', $licenses['tillypad']->td);
    $sub1 = explode('-', $sub0[0]);
    $licenses['tillypad']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
    if ($licenses['tillypad']->status_id == 0) {
        $lic_tilly = 0;
    }
    if (($licenses['tillypad']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['tillypad']->td)))) { // если лицензия в админке включена, но истёк её срок
        $lic_tilly = 3;
    }
    if (($licenses['tillypad']->status_id == 1) and (($timestamp_now + 14 * 86400) > (strtotime($licenses['tillypad']->td)))) { // если лицензия в админке включена, до окончания её срока осталось менее двух недель
        $lic_tilly = 2;
    }
    if (($licenses['tillypad']->status_id == 1) and ($timestamp_now > (strtotime($licenses['tillypad']->td)))) { // если лицензия в админке включена и до окончания её срока осталось более двух недель
        $lic_tilly = 1;
    }
}

// Блок проверки состояния лицензии Mercury

if (isset($licenses['mercury'])) {
    $sub0 = explode(' ', $licenses['mercury']->td);
    $sub1 = explode('-', $sub0[0]);
    $licenses['mercury']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
    if ($licenses['mercury']->status_id == 0) {
        $lic_merc = 0;
    }
    if (($licenses['mercury']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['mercury']->td)))) { // если лицензия в админке включена, но истёк её срок
        $lic_merc = 3;
    }
    if (($licenses['mercury']->status_id == 1) and (($timestamp_now + 14 * 86400) > (strtotime($licenses['mercury']->td)))) { // если лицензия в админке включена, до окончания её срока осталось менее двух недель
        $lic_merc = 2;
    }
    if (($licenses['mercury']->status_id == 1) and ($timestamp_now > (strtotime($licenses['mercury']->td)))) { // если лицензия в админке включена и до окончания её срока осталось более двух недель
        $lic_merc = 1;
    }
}

// Блок проверки состояния лицензии 1С

if (isset($licenses['odinsobsh'])) {
    $lic_odinsobsh = 0;
    $sub0 = explode(' ', $licenses['odinsobsh']->td);
    $sub1 = explode('-', $sub0[0]);
    $licenses['odinsobsh']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
    if ($licenses['odinsobsh']->status_id == 0) {
        $lic_odinsobsh = 0;
    }
    if (($licenses['odinsobsh']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['odinsobsh']->td)))) { // если лицензия в админке включена, но истёк её срок
        $lic_odinsobsh = 3;
    }
    if (($licenses['odinsobsh']->status_id == 1) and (($timestamp_now + 14 * 86400) > (strtotime($licenses['odinsobsh']->td)))) { // если лицензия в админке включена, до окончания её срока осталось менее двух недель
        $lic_odinsobsh = 2;
    }
    if (($licenses['odinsobsh']->status_id == 1) and ($timestamp_now > (strtotime($licenses['odinsobsh']->td)))) { // если лицензия в админке включена и до окончания её срока осталось более двух недель
        $lic_odinsobsh = 1;
    }
}
?>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Партнёры по интеграции</h3>
            </div>
            <div class="box-body" align="right">
                <?= Html::a('<i class="fa"></i> Обновить', ['getws'], ['class' => 'btn btn-md fk-button']) ?>
            </div>
            <?php if (isset($licenses['rkws'])): ?>
                <div class="box-body">
                    <div class="hpanel">
                        <div class="panel-body">
                            <div class="col-md-7 text-left">
                                <?= Html::a('<h4 class="m-b-xs text-info">R-Keeper</h4>', ['rkws/default']) ?>
                                <p class="small">Интеграция с R-keeper STORE HOUSE через White Server (облачная
                                    версия)</p>
                            </div>
                            <div class="col-md-3 text-left">
                                <?php
                                switch ($rk_us) {
                                    case 0:
                                        print "<p class=\"small\"> Лицензия MixCart: ID " . $licenses['rkws']->id . " <strong><span style=\"color:#dd4b39\">Не активна</span></strong>.</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 1:
                                        print "<p class=\"small\"> Лицензия MixCart: ID " . $licenses['rkws']->id . " <strong><span style=\"color:#dd4b39\">Не активна </span></strong>с " . $licenses['rkws']->td . ".</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 2:
                                        print "<p class=\"small\"> Лицензия MixCart: ID " . $licenses['rkws']->id . " <strong><span style=\"color:#dd4b39\">Истекает срок </span></strong>(по " . $licenses['rkws']->td . "). </br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 3:
                                        print "<p class=\"small\"> Лицензия MixCart: ID " . $licenses['rkws']->id . " <strong><span style=\"color:#6ea262\">Активна </span></strong>(по " . $licenses['rkws']->td . "). </br>";
                                        print "</p></br>";
                                        break;
                                }
                                switch ($rk_lic) {
                                    case 0:
                                        print "<p class=\"small\"> Лицензия UCS: ID " . $licenses['rkws_ucs']->code . " <strong><span style=\"color:#dd4b39\">Не активна</span></strong>.</br>";
                                        print "Пожалуйста, обратитесь к вашему <a href=\"https://www.ucs.ru/dealers/\" target=\"_blanc\">дилеру UCS</a>.</p>";
                                        break;
                                    /*case 1:
                                        print "<p class=\"small\"> Лицензия UCS: ID " . $licenses['rkws_ucs']->code . " <strong><span style=\"color:#dd4b39\">Не активна </span></strong>с " . $licenses['rkws_ucs']->td . ".</br>";
                                        print "Пожалуйста, обратитесь к вашему <a href=\"https://www.ucs.ru/dealers/\" target=\"_blanc\">дилеру UCS</a>.</p>";
                                        break;
                                    case 2:
                                        print "<p class=\"small\"> Лицензия UCS: ID " . $licenses['rkws_ucs']->code . " <strong><span style=\"color:#dd4b39\">Истекает срок </span></strong>(по " . $licenses['rkws_ucs']->td . "). </br>";
                                        print "Пожалуйста, обратитесь к вашему <a href=\"https://www.ucs.ru/dealers/\" target=\"_blanc\">дилеру UCS</a>.</p>";
                                        break;*/
                                    case 3:
                                        print "<p class=\"small\"> Лицензия UCS: ID " . $licenses['rkws_ucs']->code . " <strong><span style=\"color:#6ea262\">Активна </span></strong></br>";
                                        print "</p></br>";
                                        break;
                                }
                                ?>

                            </div>
                            <div class="col-md-2 text-right">
                                <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'], ['class' => 'btn btn-default btn-sm m-t']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($licenses['iiko'])): ?>
                <div class="box-body">
                    <div class="hpanel">
                        <div class="panel-body">
                            <div class="col-md-7 text-left">
                                <?= Html::a('<h4 class="m-b-xs text-info">iiko Office</h4>', ['iiko/default']) ?>
                                <p class="small">Интеграция с iiko Office</p>
                            </div>
                            <div class="col-md-3 text-left">
                                <?php
                                switch ($lic_iiko) {
                                    case 0:
                                        print "<p class=\"small\"> Лицензия IIKO: ID " . $licenses['iiko']->id . " <strong><span style=\"color:#dd4b39\">Не активна</span></strong>.</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 1:
                                        print "<p class=\"small\"> Лицензия IIKO: ID " . $licenses['iiko']->id . " <strong><span style=\"color:#dd4b39\">Не активна </span></strong>с " . $licenses['iiko']->td . ".</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 2:
                                        print "<p class=\"small\"> Лицензия IIKO: ID " . $licenses['iiko']->id . " <strong><span style=\"color:#dd4b39\">Истекает срок </span></strong>(по " . $licenses['iiko']->td . "). </br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 3:
                                        print "<p class=\"small\"> Лицензия IIKO: ID " . $licenses['iiko']->id . " <strong><span style=\"color:#6ea262\">Активна </span></strong>(по " . $licenses['iiko']->td . "). </br>";
                                        print "</p></br>";
                                        break;
                                }
                                ?>
                            </div>
                            <div class="col-md-2 text-right">
                                <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'], ['class' => 'btn btn-default btn-sm m-t']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($licenses['odinsobsh'])): ?>
                <div class="box-body">
                    <div class="hpanel">
                        <div class="panel-body">
                            <div class="col-md-7 text-left">
                                <?= Html::a('<h4 class="m-b-xs text-info">1C Общепит</h4>', ['odinsobsh/default']) ?>
                                <p class="small">Интеграция с 1С Общепит</p>
                            </div>
                            <div class="col-md-3 text-left">
                                <?php
                                switch ($lic_odinsobsh) {
                                    case 0:
                                        print "<p class=\"small\"> Лицензия 1C Общепит: ID " . $licenses['odinsobsh']->id . " <strong><span style=\"color:#dd4b39\">Не активна</span></strong>.</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 1:
                                        print "<p class=\"small\"> Лицензия Общепит: ID " . $licenses['odinsobsh']->id . " <strong><span style=\"color:#dd4b39\">Не активна </span></strong>с " . $licenses['odinsobsh']->td . ".</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 2:
                                        print "<p class=\"small\"> Лицензия Общепит: ID " . $licenses['odinsobsh']->id . " <strong><span style=\"color:#dd4b39\">Истекает срок </span></strong>(по " . $licenses['odinsobsh']->td . "). </br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 3:
                                        print "<p class=\"small\"> Лицензия Общепит: ID " . $licenses['odinsobsh']->id . " <strong><span style=\"color:#6ea262\">Активна </span></strong>(по " . $licenses['odinsobsh']->td . "). </br>";
                                        print "</p></br>";
                                        break;
                                }
                                ?>
                            </div>
                            <div class="col-md-2 text-right">
                                <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'], ['class' => 'btn btn-default btn-sm m-t']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($licenses['tillypad'])): ?>
                <div class="box-body">
                    <div class="hpanel">
                        <div class="panel-body">
                            <div class="col-md-7 text-left">
                                <?= Html::a('<h4 class="m-b-xs text-info">Tillypad</h4>', ['tillypad/default']) ?>
                                <p class="small">Интеграция с Tillypad</p>
                            </div>
                            <div class="col-md-3 text-left">
                                <?php
                                switch ($lic_tilly) {
                                    case 0:
                                        print "<p class=\"small\"> Лицензия Tillypad: ID " . $licenses['tillypad']->id . " <strong><span style=\"color:#dd4b39\">Не активна</span></strong>.</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 1:
                                        print "<p class=\"small\"> Лицензия Tillypad: ID " . $licenses['tillypad']->id . " <strong><span style=\"color:#dd4b39\">Не активна </span></strong>с " . $licenses['tillypad']->td . ".</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 2:
                                        print "<p class=\"small\"> Лицензия Tillypad: ID " . $licenses['tillypad']->id . " <strong><span style=\"color:#dd4b39\">Истекает срок </span></strong>(по " . $licenses['tillypad']->td . "). </br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 3:
                                        print "<p class=\"small\"> Лицензия Tillypad: ID " . $licenses['tillypad']->id . " <strong><span style=\"color:#6ea262\">Активна </span></strong>(по " . $licenses['tillypad']->td . "). </br>";
                                        print "</p></br>";
                                        break;
                                }
                                ?>
                            </div>
                            <div class="col-md-2 text-right">
                                <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'], ['class' => 'btn btn-default btn-sm m-t']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php // if(isset($licenses['email'])): ?>
            <div class="box-body">
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">Накладные поставщика</h4>', ['email/default']) ?>
                            <p class="small">Загрузка накладных из 1С с помощью EMAIL</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'], ['class' => 'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php // endif; ?>
            <?php if (isset($licenses['mercury'])): ?>
                <div class="box-body">
                    <div class="hpanel">
                        <div class="panel-body">
                            <div class="col-md-7 text-left">
                                <?= Html::a('<h4 class="m-b-xs text-info"> ' . Yii::t('message', 'frontend.client.integration.mercury.title', ['ru' => 'ВЕТИС "Меркурий"']) . '</h4>', ['merc/settings']) ?>
                                <p class="small"><?= Yii::t('message', 'frontend.client.integration.mercury', ['ru' => 'Интеграция с системой ВЕТИС "Меркурий"']) ?></p>
                            </div>
                            <div class="col-md-3 text-left">
                                <?php
                                switch ($lic_merc) {
                                    case 0:
                                        print "<p class=\"small\"> Лицензия ВЕТИС Меркурий: ID " . $licenses['mercury']->id . " <strong><span style=\"color:#dd4b39\">Не активна</span></strong>.</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 1:
                                        print "<p class=\"small\"> Лицензия ВЕТИС Меркурий: ID " . $licenses['mercury']->id . " <strong><span style=\"color:#dd4b39\">Не активна </span></strong>с " . $licenses['mercury']->td . ".</br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 2:
                                        print "<p class=\"small\"> Лицензия ВЕТИС Меркурий: ID " . $licenses['mercury']->id . " <strong><span style=\"color:#dd4b39\">Истекает срок </span></strong>(по " . $licenses['mercury']->td . "). </br>";
                                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                                        break;
                                    case 3:
                                        print "<p class=\"small\"> Лицензия ВЕТИС Меркурий: ID " . $licenses['mercury']->id . " <strong><span style=\"color:#6ea262\">Активна </span></strong>(по " . $licenses['mercury']->td . "). </br>";
                                        print "</p></br>";
                                        break;
                                }
                                ?>
                            </div>
                            <div class="col-md-2 text-right">
                                <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> ' . Yii::t('message', 'frontend.client.integration.mercury.documentation', ['ru' => 'Документация']), ['#'], ['class' => 'btn btn-default btn-sm m-t']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
</section>

