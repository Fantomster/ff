<?php

/**
 * Class RkwsAgent
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use yii\web\BadRequestHttpException;
use common\models\OuterAgent;
use api_web\modules\integration\classes\SyncLog;

class RkwsAgent extends ServiceRkws
{
    /** @var string $index Символьный идентификатор справочника */
    public $index = self::DICTIONARY_AGENT;

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterAgent::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_corrs';

    /** @var array $additionalXmlFields Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['name' => 'name'];

    /**
     * @param string|null $data
     * @return array
     * @throws BadRequestHttpException
     */
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
        foreach ($this->iterator($myXML->CORRGROUP) as $corrgroup) {
            foreach ($this->iterator($corrgroup->CORR) as $corr) {
                $gcount++;
                foreach ($corr->attributes() as $k => $v) {
                    $array[$gcount][$k] = strval($v[0]);
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
