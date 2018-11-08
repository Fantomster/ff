<?php

/**
 * Class RkwsAgent
 *
 * @package   api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */

namespace api_web\modules\integration\classes\sync;

use common\models\Waybill;
use yii\web\BadRequestHttpException;
use api_web\modules\integration\classes\SyncLog;

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

    public function makeArrayFromReceivedDictionaryXmlData(string $data = null): array
    {
        $myXML = simplexml_load_string($data);
        SyncLog::trace('XML data: ' . $data . PHP_EOL . ' ---------------- ' . PHP_EOL);
        if (!$myXML) {
            SyncLog::trace('Empty XML data!');
            throw new BadRequestHttpException("empty_result_xml_data");
        }
        $array = [];
        $gcount = 0;

        if (!isset($myXML->ERROR)) {
            //$cmdguid = strval($myXML['cmdguid']);
            $array['stat'] = 3;
            foreach ($myXML->DOC as $doc) {
                foreach ($doc->attributes() as $a => $b) {
                    $array[$gcount][$a] = strval($b[0]);
                }

            }

        } else {
            //$cmdguid = strval($myXML['taskguid']);
            $array['stat'] = 4;
            foreach ($myXML->ERROR as $doc) {
                foreach ($doc->attributes() as $a => $b) {
                    $array[$gcount][$a] = strval($b[0]);
                }

            }
        }
        return $array;
    }
}
