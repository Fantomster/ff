<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\dicts;

use common\models\vetis\VetisPurpose;
use common\models\vetis\VetisUnit;
use frontend\modules\clientintegr\modules\merc\helpers\api\baseApi;
use Yii;
use yii\db\Exception;

class dictsApi extends baseApi
{

    public function init()
    {
        $this->system = 'dicts';
        $this->wsdlClassName = Dicts::class;
        parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * Получение цели по Guid
     * @param $GUID
     * @return \common\models\vetis\VetisPurpose|null
     */
    public function getPurposeByGuid($GUID)
    {
        $purpose = VetisPurpose::findOne(['guid' => $GUID]);

        if (!empty($purpose)) {
            return unserialize($purpose->data);
        }

        VetisPurpose::getUpdateData($this->org_id);
        return null;
    }
    /**
     * Получение еденицы измерения по Guid
     * @param $GUID
     * @return \common\models\vetis\VetisUnit|null
     */
    public function getUnitByGuid($GUID)
    {
        $unit = VetisUnit::findOne(['guid' => $GUID]);

        if (!empty($unit)) {
            return unserialize($unit->data);
        }

        VetisUnit::getUpdateData($this->org_id);
        return null;
    }

    /**
     * Получение списка едениц измерения
     * @return \common\models\vetis\VetisUnit[]|null
     */
    public function getUnitList()
    {
        $units = VetisUnit::findAll(['active' => 1, 'last' => 1]);

        if (!empty($units)) {
            $list = [];
            foreach ($units as $item)
            {
                $list[] = unserialize($item->data);
            }
            return $list;
        }

        VetisUnit::getUpdateData($this->org_id);
        return [];
    }

    /**
     * Получение списка целей
     * @return \common\models\vetis\VetisUnit[]|null
     */
    public function getPurposeList()
    {
        $purposes = VetisPurpose::findAll(['active' => 1, 'last' => 1]);

        if (!empty($purposes)) {
            $list = [];
            foreach ($purposes as $item)
            {
               $list[] = unserialize($item->data);
            }
            return $list;
        }

        VetisPurpose::getUpdateData($this->org_id);
        return [];
    }

    /**
     * Составление запроса на загрузку справочника едениц изхмерения
     * @param $options
     * @return getUnitChangesListRequest
     * @throws Exception
     */
    public function getUnitChangesList($options)
    {
        require_once (__DIR__ ."/Dicts.php");
        $request = new getUnitChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception('startDate field is not specified');
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

    /**
     * Составление запроса на загрузку справочника целей
     * @param $options
     * @return getPurposeChangesListRequest
     * @throws Exception
     */
    public function getPurposeChangesList($options)
    {
        require_once (__DIR__ ."/Dicts.php");
        $request = new getPurposeChangesListRequest();
        $request->listOptions = $options['listOptions'];

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception('startDate field is not specified');
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }
}