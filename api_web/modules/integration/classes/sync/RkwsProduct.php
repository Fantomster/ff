<?php

/**
 * Class RkwsProduct
 *
 * @package   api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */

namespace api_web\modules\integration\classes\sync;

use api_web\modules\integration\classes\SyncLog;
use common\models\OuterProduct;
use yii\web\BadRequestHttpException;

class RkwsProduct extends ServiceRkws
{
    /** @var string $index Символьный идентификатор справочника */
    public $index = self::DICTIONARY_PRODUCT;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_goods';

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterProduct::class;

    /** @var array $additionalXmlFields Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['name' => 'name', 'outer_uid' => 'outer_unit_id'];

    public function makeArrayFromReceivedDictionaryXmlData(string $data = null): array
    {
        $myXML = simplexml_load_string($data);
        SyncLog::trace('XML data: ' . $data . PHP_EOL . ' ---------------- ' . PHP_EOL);
        if (!$myXML) {
            SyncLog::trace('Empty XML data!');
            throw new BadRequestHttpException("empty_result_xml_data");
        }
        $array = [];
        $pcount = 0;
        foreach ($this->iterator($myXML->ITEM) as $group) {
            if (!isset($group->GOODS_LIST) OR empty($group->GOODS_LIST)) {
                continue;
            }
            foreach ($this->iterator($group->GOODS_LIST->ITEM) as $product) {
                $pcount++;
                foreach ($product->attributes() as $k => $v) {
                    $array[$pcount][$k] = strval($v[0]);
                }
                $array[$pcount]['outer_uid'] = null;
                foreach ($product->MUNITS as $unit) {
                    foreach ($unit->MUNIT as $v) {
                        $array[$pcount]['outer_uid'] = strval($v->attributes()['rid'][0]);
                    }
                }
            }
        }
        if (!$array) {
            SyncLog::trace('Wrong XML data!');
            throw new BadRequestHttpException("wrong_xml_data");
        }
        return $array;
    }

    /**
     * @param $items
     * @return \Generator
     */
    private function iterator($items)
    {
        foreach ($items as $item) {
            yield $item;
        }
    }
}
