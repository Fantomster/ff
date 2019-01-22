<?php

namespace api_web\modules\integration\classes\sync\rkws;

use api_web\modules\integration\classes\sync\ServiceRkws;
use common\models\Waybill;
use yii\web\BadRequestHttpException;

class RkwsWaybill extends ServiceRkws
{

    /** @var string $index Символьный идентификатор справочника */
    public $index = 'waybill';

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = Waybill::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_doc_receiving_report';

    /** @var array $additionalXmlFields Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['name' => 'name'];

    /**
     * @param string|null $data
     * @return array
     * @throws BadRequestHttpException
     */
    public function parsingXml(string $data = null): array
    {
        $myXML = simplexml_load_string($data);
        $this->log('XML data: ' . $data . PHP_EOL . ' ---------------- ' . PHP_EOL);
        if (!$myXML) {
            throw new BadRequestHttpException("empty_result_xml_data");
        }
        $array = [];
        $gcount = 0;

        if (!isset($myXML->ERROR)) {
            $array['stat'] = 3;
            foreach ($myXML->DOC as $doc) {
                foreach ($doc->attributes() as $a => $b) {
                    $array[$gcount][$a] = strval($b[0]);
                }
            }
        } else {
            $array['stat'] = 4;
            foreach ($myXML->ERROR as $doc) {
                foreach ($doc->attributes() as $a => $b) {
                    $array[$gcount][$a] = strval($b[0]);
                }
            }
        }
        return $array;
    }

    /**
     * @param $items
     * @param $shVersion
     * @return array
     */
    public static function prepareItemsWaybill($items, $shVersion)
    {
        $result = [];
        /** @method getItemSh4 $method */
        /** @method getItemSh5 $method */
        $method = 'getItemSh' . $shVersion;
        if (is_iterable($items)) {
            foreach ($items as $record) {
                $result[] = self::{$method}($record);
            }
        }
        return $result;
    }

    /**
     * @param $rec
     * @return array
     */
    private static function getItemSh4($rec)
    {
        $vatSum = round(($rec['sum_without_vat'] * ($rec['vat_waybill'] / 100)) * 100, 0);

        return [
            'mu'      => $rec["unit_rid"],
            'quant'   => ($rec["quantity_waybill"] * 1000),
            'rid'     => $rec['product_rid'],
            'sum'     => ($rec['sum_without_vat'] * 100),
            'vatrate' => ($rec['vat_waybill'] * 100),
            'vatsum'  => $vatSum
        ];
    }

    /**
     * @param $rec
     * @return array
     */
    private static function getItemSh5($rec)
    {
        $vatSumm = ($rec['sum_without_vat'] * ($rec['vat_waybill'] / 100));
        return [
            'mu'      => $rec["unit_rid"],
            'quant'   => ($rec["quantity_waybill"] * 1000),
            'rid'     => $rec['product_rid'],
            'vatrate' => ($rec['vat_waybill'] * 100),
            'sum'     => str_replace('.', ',', round($rec['sum_without_vat'], 2)),
            'vatsum'  => str_replace('.', ',', round($vatSumm, 2)),
        ];
    }

}
