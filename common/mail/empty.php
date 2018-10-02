<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
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
        $content = 'Возможно, в Вашей накладной ошибка. Просим проверить. ';
        $content .= 'В вашем письме, отправленном  ' . $this->invoice->nameConsignee . ' во вложенном файле накладной ';
        $content .= $name_file . ' есть ошибки. Суммы, указанные в итоге накладной, не совпадают с подсчитанной суммой всех строк накладной. ';
        $content .= 'Сумма накладной без НДС - ' . $this->sumWithoutTaxExcel . ' а сумма без НДС всех строк накладной равна ' . $this->invoice->price_without_tax_sum;
        $content .= ' Сумма накладной c НДС - ' . $this->sumWithTaxExcel . ' а сумма c НДС всех строк накладной равна ' . $this->invoice->price_with_tax_sum;
        $content .= ' Просим обратить внимание на ошибку и подтвердить достоверность передаваемых данных.';
        ?>
        <?= $content ?>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>