<?php

namespace common\components;

//use golovchanskiy\parse-torg12\models\invoice;

/**
 * Товарная накладная
 */
class MyInvoice// extends Invoice
{

    /**
     * Номер накладной
     *
     * @var string
     */
    public $number;

    /**
     * Дата накладной
     *
     * @var string
     */
    public $date;

    /**
     * Сумма накладной без учета НДС
     *
     * @var float
     */
    public $price_without_tax_sum = 0;

    /**
     * Сумма накладной с учетом НДС
     *
     * @var float
     */
    public $price_with_tax_sum = 0;

    /**
     * Строки накладной
     *
     * @var InvoiceRow[]
     */

    public $rows = [];
    /**
     * Массив координат ячеек, содержащих имя поставщика в накладной
     *
     * @var array
     */

    public $tmpMassivsNames;

    /**
     * Массив координат ячеек, содержащих ИНН и КПП (если есть) поставщика в накладной
     *
     * @var array
     */
    public $tmpMassivsInns;

}