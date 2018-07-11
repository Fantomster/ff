<?php

/**
 * @var integer $vsd_count
 */

?>
<div style="width: 100%; text-align: center;"><?= Yii::t('app', 'common.mail.merc_vsd.vsd_count', ['ru'=>'Количество непогашенных ВСД'], 'ru') ?>: <?= $vsd_count ?></div>
<br />
<div style="width: 100%; text-align: center;">
    <a href="<?= Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/clientintegr/merc/default"]); ?>"
       style="text-decoration: none;
    color: #FFF;
    background-color: #84bf76;
    padding: 10px 16px;
    font-weight: bold;
    margin-right: 10px;
    text-align: center;
    cursor: pointer;
    display: inline-block;
    border-radius: 4px;
    width: 80%;"><?= Yii::t('app', 'common.mail.merc_vsd.process_button', ['ru'=>'Перейти к гашению'], 'ru') ?></a>
</div>