<?php

namespace api_web\modules\integration\classes\sync\rkws;

use api_web\modules\integration\classes\sync\ServiceRkws;
use common\models\OuterUnit;
use yii\web\BadRequestHttpException;

class RkwsUnit extends ServiceRkws
{
    public $index = self::DICTIONARY_UNIT;
    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterUnit::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_munits';

    /** @var array $additionalXmlFields Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['name' => 'name', 'ratio' => 'ratio'];

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
        foreach ($this->iterator($myXML->ITEM) as $unit_group) {
            $parent = $unit_group->attributes()['rid'];
            foreach ($unit_group->attributes() as $k => $v) {
                $array['_' . $parent][$k] = strval($v[0]);
            }
            $array['_' . $parent]['parent'] = '';
            foreach ($this->iterator($unit_group->MUNITS_LIST) as $list) {
                foreach ($this->iterator($list->ITEM) as $item) {
                    $i = $item->attributes()['rid'];
                    foreach ($item->attributes() as $k => $v) {
                        $array[(string)$parent . '_' . (string)$i][$k] = strval($v[0]);
                    }
                    $array[(string)$parent . '_' . (string)$i]['parent'] = (string)$parent;
                }
            }
        }
        if (!$array) {
            throw new BadRequestHttpException("wrong_xml_data");
        }
        return $array;
    }

}
