<?php

namespace api_web\modules\integration\classes\sync;

use common\models\OuterStore;
use yii\web\BadRequestHttpException;

class RkwsStore extends ServiceRkws
{
    /** @var string $index Символьный идентификатор справочника */
    public $index = self::DICTIONARY_STORE;

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterStore::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_stores';

    /** @var bool */
    public $useNestedSets = true;

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
        $scount = 0;
        foreach ($this->iterator($myXML->STOREGROUP) as $storegroup) {
            $scount++;
            $grid = (string)$storegroup->attributes()->rid;
            $array[$scount]['rid'] = $grid;
            $array[$scount]['name'] = (string)$storegroup->attributes()->name;
            $array[$scount]['parent'] = null;
            foreach ($this->iterator($storegroup->STORE) as $store) {
                $scount++;
                foreach ($store->attributes() as $k => $v) {
                    $array[$scount][$k] = strval($v[0]);
                    $array[$scount]['parent'] = (string)$storegroup->attributes()->rid;
                }
            }
        }
        if (!$array) {
            throw new BadRequestHttpException("wrong_xml_data");
        }
        return $array;
    }
}
