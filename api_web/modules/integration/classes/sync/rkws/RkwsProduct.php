<?php

namespace api_web\modules\integration\classes\sync\rkws;

use api_web\modules\integration\classes\sync\ServiceRkws;
use common\models\OuterCategory;
use common\models\OuterProduct;
use common\models\OuterUnit;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class RkwsProduct extends ServiceRkws
{
    public $index = self::DICTIONARY_PRODUCT;
    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_goods';

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterProduct::class;

    /** @var array $additionalXmlFields Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['name' => 'name', 'outer_unit_id' => 'outer_unit_id'];

    /**
     * @param string|null $data
     * @return array
     * @throws BadRequestHttpException
     */
    public function parsingXml(string $data = null): array
    {
        $dictionary = $this->getOrganizationDictionary($this->serviceId, $this->orgId);

        $myXML = simplexml_load_string($data);
        $this->log('XML data: ' . $data . PHP_EOL . ' ---------------- ' . PHP_EOL);
        if (!$myXML) {
            $dictionary->status_id = $dictionary::STATUS_ERROR;
            $dictionary->save();
            throw new BadRequestHttpException("empty_result_xml_data");
        }

        $units = OuterUnit::find()
                ->where([
                    'service_id' => $this->serviceId,
                    'org_id'     => $this->orgId
                ])->asArray()
                ->all();
        if (!empty($units)) {
            $units = ArrayHelper::map($units, 'outer_uid', 'id');
        }

        $selectedGategory = OuterCategory::find()
                ->where([
                    'service_id' => $this->serviceId,
                    'org_id'     => $this->orgId,
                    'selected'   => 1,
                    'is_deleted' => 0
                ])->asArray()
                ->all();
        if (!empty($selectedGategory)) {
            $selectedGategory = ArrayHelper::map($selectedGategory, 'outer_uid', 'id');
        }

        $array  = [];
        $pcount = 0;
        /** @var \SimpleXMLElement $group */
        foreach ($this->iterator($myXML->ITEM) as $group) {
            $group_rid = ((array) $group->attributes())['@attributes']['rid'];
            if (!isset($selectedGategory[$group_rid])) {
                continue;
            }
            if (!isset($group->GOODS_LIST) OR empty($group->GOODS_LIST)) {
                continue;
            }
            foreach ($this->iterator($group->GOODS_LIST->ITEM) as $product) {
                $pcount++;
                foreach ($product->attributes() as $k => $v) {
                    $array[$pcount][$k] = strval($v[0]);
                }
                $array[$pcount]['outer_unit_id'] = null;
                foreach ($this->iterator($product->MUNITS) as $unit) {
                    foreach ($this->iterator($unit->MUNIT) as $v) {
                        if ($v->attributes()['isbase'][0] == 1) {
                            $unit_rid = trim(strval($v->attributes()['rid'][0]));
                            if (isset($units[$unit_rid])) {
                                $unit_id = $units[$unit_rid];
                            } else {
                                $unitModel             = new OuterUnit();
                                $unitModel->org_id     = $this->orgId;
                                $unitModel->service_id = $this->serviceId;
                                $unitModel->name       = strval($v->attributes()['name'][0]);
                                $unitModel->outer_uid  = $unit_rid;
                                $unitModel->save();
                                $unit_id               = $unitModel->id;
                                $units[$unit_rid]      = $unitModel->id;
                            }
                            $array[$pcount]['outer_unit_id'] = $unit_id;
                            break;
                        }
                    }
                }
            }
        }

        return $array;
    }

}
