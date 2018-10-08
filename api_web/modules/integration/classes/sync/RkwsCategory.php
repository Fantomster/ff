<?php

/**
 * Class RkwsCategory
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @updateddAt 2018-10-08
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use yii\web\BadRequestHttpException;
use common\models\OuterCategory;
use api_web\modules\integration\classes\SyncLog;

class RkwsCategory extends ServiceRkws
{

    /** @var string $index Символьный идентификатор справочника */
    public $index = 'category';

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterCategory::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_goodgroups';

    /** @var array $additionalXmlFields Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['name' => 'name', 'parent' => 'parent_outer_uid'];

    public function makeArrayFromReceivedDictionaryXmlData(string $data = null): array
    {
        $myXML = simplexml_load_string($data);
        SyncLog::trace('XML data: ' . $data . PHP_EOL . ' ---------------- ' . PHP_EOL);
        if (!$myXML) {
            SyncLog::trace('Empty XML data!');
            throw new BadRequestHttpException("empty_result_xml_data");
        }
        $array = [];
        foreach ($myXML->ITEM as $category) {
            foreach ($category->attributes() as $k => $v) {
                $array[''][$k] = strval($v[0]);
            }
        }
        if (!$array) {
            SyncLog::trace('Wrong XML data!');
            throw new BadRequestHttpException("wrong_xml_data");
        }
        return $array;
    }

}
