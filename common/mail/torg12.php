<p><?= Yii::t('message', 'common.mail.error.errorsum1', ['ru' => 'Возможно, в Вашей накладной ошибка. Просим проверить.']) ?></p>
<p>
    <?= Yii::t('message', 'common.mail.error.errorsum2', ['ru' => 'В вашем письме, отправленном']) . " $invoice->nameConsignee " . Yii::t('message', 'common.mail.error.errorsum3', ['ru' => 'во вложенном файле накладной']) ?>
    <?= " $name_file " . Yii::t('message', 'common.mail.error.errorsum4', ['ru' => 'есть ошибки. Суммы, указанные в итоге накладной, не совпадают с подсчитанной суммой всех строк накладной.']) ?>
</p>
<p><?= Yii::t('message', 'common.mail.error.errorsum5', ['ru' => 'Сумма накладной без НДС -']) . " $sumWithoutTaxExcel" . Yii::t('message', 'common.mail.error.errorsum6', ['ru' => ', а сумма без НДС всех строк накладной равна']) . " $invoice->price_without_tax_sum" ?></p>
<p><?= Yii::t('message', 'common.mail.error.errorsum7', ['ru' => 'Сумма накладной c НДС -']) . " $sumWithTaxExcel" . Yii::t('message', 'common.mail.error.errorsum8', ['ru' => ', а сумма c НДС всех строк накладной равна']) . " $invoice->price_with_tax_sum" ?></p>
<p><?= Yii::t('message', 'common.mail.error.errorsum9', ['ru' => 'Просим обратить внимание на ошибку и подтвердить достоверность передаваемых данных.']) ?></p>