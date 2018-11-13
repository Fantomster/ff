<?php

namespace api_web\modules\integration\classes\sync;

use yii\web\BadRequestHttpException;
use common\models\OuterCategory;
use api_web\modules\integration\classes\SyncLog;

class RkwsCategory extends ServiceRkws
{
    /** @var string $index Символьный идентификатор справочника */
    public $index = self::DICTIONARY_CATEGORY;

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterCategory::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_goodgroups';

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
        $ccount = 0;
        foreach ($this->iterator($myXML->ITEM) as $category) {
            $ccount++;
            foreach ($category->attributes() as $k => $v) {
                $array[$ccount][$k] = strval($v[0]);
            }
        }
        if (!$array) {
            SyncLog::trace('Wrong XML data!');
            throw new BadRequestHttpException("wrong_xml_data");
        }
        return $array;
    }
}
