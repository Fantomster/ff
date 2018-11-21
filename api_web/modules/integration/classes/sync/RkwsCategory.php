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

    /** @var array Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['parent' => 'parent_outer_uid'];

    public function parsingXml(string $data = null): array
    {
        $myXML = simplexml_load_string($data);
        if (!$myXML) {
            SyncLog::trace('Empty XML data!');
            throw new BadRequestHttpException("empty_result_xml_data");
        }
        $array = [];
        foreach ($this->iterator($myXML->ITEM) as $category) {
            $attr = (array)$category->attributes();
            $array[] = [
                'rid'    => $attr['@attributes']['rid'],
                'name'   => $attr['@attributes']['name'],
                'parent' => $attr['@attributes']['parent'],
            ];
        }
        if (!$array) {
            SyncLog::trace('Не удалось извлечь данные из XML.');
            throw new BadRequestHttpException("wrong_xml_data");
        }

        return $array;
    }
}
