<?php

/**
 * @var integer $vsd_count
 */

?>
<div style="width: 100%; text-align: center;"><?= Yii::t('app', 'common.mail.merc_stock_expiry.message_text', ['ru'=>'Согласно журналу продукции ГИС Меркурий на вашем складе присутствует испорченная продукция. Настоятельно рекомендуем вам произвести инвентаризацию'], 'ru') ?></div>
<br />
<div style="width: 100%; text-align: center;">
    <a href="<?= Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/clientintegr/merc/stock-entry", "mercStockEntrySearch[is_expiry]" => 0]); ?>"
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
    width: 80%;"><?= Yii::t('app', 'common.mail.merc_stock_expiry.process_button', ['ru'=>'Перейти к журналу продукции'], 'ru') ?></a>
</div>