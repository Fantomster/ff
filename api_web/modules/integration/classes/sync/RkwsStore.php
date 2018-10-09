<?php

/**
 * Class RkwsStore
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use api_web\modules\integration\classes\SyncLog;
use common\models\OuterStore;
use yii\web\BadRequestHttpException;

class RkwsStore extends ServiceRkws
{

    /** @var string $index Символьный идентификатор справочника */
    public $index = 'store';

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterStore::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_stores';

    public $useNestedSets = true;

    public function makeArrayFromReceivedDictionaryXmlData(string $data = null): array
    {
        $myXML = simplexml_load_string($data);
        SyncLog::trace('XML data: ' . $data . PHP_EOL . ' ---------------- ' . PHP_EOL);
        if (!$myXML) {
            SyncLog::trace('Empty XML data!');
            throw new BadRequestHttpException("empty_result_xml_data");
        }
        $array = [];
        $scount = 0;
        foreach ($myXML->STOREGROUP as $storegroup) {
            $scount++;
            $grid = (string)$storegroup->attributes()->rid;
            $array[$scount]['rid'] = $grid;
            $array[$scount]['name'] = (string)$storegroup->attributes()->name;
            $array[$scount]['parent'] = null;
            foreach ($storegroup->STORE as $store) {
                $scount++;
                foreach ($store->attributes() as $k => $v) {
                    $array[$scount][$k] = strval($v[0]);
                    $array[$scount]['parent'] = (string)$storegroup->attributes()->rid;
                }
            }
        }
        if (!$array) {
            SyncLog::trace('Wrong XML data!');
            throw new BadRequestHttpException("wrong_xml_data");
        }
        return $array;
    }
}
