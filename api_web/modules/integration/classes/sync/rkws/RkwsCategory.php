<?php

namespace api_web\modules\integration\classes\sync\rkws;

use api_web\modules\integration\classes\sync\ServiceRkws;
use yii\web\BadRequestHttpException;
use common\models\OuterCategory;

class RkwsCategory extends ServiceRkws
{
    public $index = self::DICTIONARY_CATEGORY;
    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterCategory::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_goodgroups';

    public $useNestedSets = true;

    /** @var array Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['parent' => 'parent_outer_uid'];

    /**
     * @param string|null $data
     * @return array
     * @throws BadRequestHttpException
     */
    public function parsingXml(string $data = null): array
    {
        $myXML = simplexml_load_string($data);
        if (!$myXML) {
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
            throw new BadRequestHttpException("wrong_xml_data");
        }

        return $array;
    }
}
