<?php

namespace common\components;

use golovchanskiy\parseTorg12\models\Invoice;
use yii\db\Query;

/**
 * Товарная накладная
 */
class Torg12Invoice extends Invoice
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

    /**
     * Наименование поставщика вместе с формой собственности, приведённое к верхнему регистру
     *
     * @var string
     */
    public $namePostav;

    /**
     * ИНН поставщика
     *
     * @var string
     */
    public $innPostav;

    /**
     * КПП поставщика (если есть)
     *
     * @var string
     */
    public $kppPostav;

    /**
     * Возвращает по ID накладной сумму без НДС
     *
     */
    public function getSumWithoutNdsById($id)
    {
        $result = (new Query())->select(['invoice_relation','total_price'])->from('order')->where(['id' => $id])->one();
        if (is_null($result['invoice_relation'])) {
            $res = $result['total_price'];
        } else {
            $result2 = (new Query())->select('total_sum_withouttax')->from('integration_invoice')->where(['id' => $result['invoice_relation']])->one();
            $res = $result2['total_sum_withouttax'];
        }
        return $res;
    }

}