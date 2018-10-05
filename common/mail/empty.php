<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>MixCart</title>
        <!--[if mso]>
        <style type="text/css">
            p {
                margin-top: 10px;
                margin-bottom: 10px;
            }
        </style>
        <![end if]-->
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody();
    $content = Yii::t('message', 'common.mail.error.errorsum1', ['ru' => 'Возможно, в Вашей накладной ошибка. Просим проверить. ']);
    $content .= Yii::t('message', 'common.mail.error.errorsum2', ['ru' => 'В вашем письме, отправленном  ']) . $this->invoice->nameConsignee . Yii::t('message', 'common.mail.error.errorsum3', ['ru' => ' во вложенном файле накладной ']);
    $content .= $name_file . Yii::t('message', 'common.mail.error.errorsum4', ['ru' => ' есть ошибки. Суммы, указанные в итоге накладной, не совпадают с подсчитанной суммой всех строк накладной. ']);
    $content .= Yii::t('message', 'common.mail.error.errorsum5', ['ru' => 'Сумма накладной без НДС - ']) . $this->sumWithoutTaxExcel . Yii::t('message', 'common.mail.error.errorsum6', ['ru' => ' а сумма без НДС всех строк накладной равна ']) . $this->invoice->price_without_tax_sum;
    $content .= Yii::t('message', 'common.mail.error.errorsum7', ['ru' => ' Сумма накладной c НДС - ']) . $this->sumWithTaxExcel . Yii::t('message', 'common.mail.error.errorsum8', ['ru' => ' а сумма c НДС всех строк накладной равна ']) . $this->invoice->price_with_tax_sum;
    $content .= Yii::t('message', 'common.mail.error.errorsum9', ['ru' => ' Просим обратить внимание на ошибку и подтвердить достоверность передаваемых данных.']);
    ?>
    <?= $content ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>