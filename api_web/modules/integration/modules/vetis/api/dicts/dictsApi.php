<?php

namespace api_web\modules\integration\modules\vetis\api\dicts;

use common\models\vetis\VetisPurpose;
use common\models\vetis\VetisUnit;
use api_web\modules\integration\modules\vetis\api\baseApi;

/**
 * Class dictsApi
 *
 * @package api_web\modules\integration\modules\vetis\api\dicts
 */
class dictsApi extends baseApi
{

    /**
     *
     */
    public function init()
    {
        $this->system = 'dicts';
        $this->wsdlClassName = Dicts::class;
        parent::init();
    }

    /**
     * Получение цели по Guid
     * @param $GUID
     * @return \common\models\vetis\VetisPurpose|null
     */
    public function getPurposeByGuid($GUID)
    {
        VetisPurpose::getUpdateData($this->org_id);

        $purpose = VetisPurpose::findOne(['guid' => $GUID]);

        if (!empty($purpose)) {
            return unserialize($purpose->data);
        }

        return null;
    }
    /**
     * Получение еденицы измерения по Guid
     * @param $GUID
     * @return \common\models\vetis\VetisUnit|null
     */
    public function getUnitByGuid($GUID)
    {
        VetisUnit::getUpdateData($this->org_id);

        $unit = VetisUnit::findOne(['guid' => $GUID]);

        if (!empty($unit)) {
            return unserialize($unit->data);
        }

        return null;
    }

    /**
     * Получение списка едениц измерения
     * @return \common\models\vetis\VetisUnit[]|null
     */
    public function getUnitList()
    {
        VetisUnit::getUpdateData($this->org_id);

        $units = VetisUnit::findAll(['active' => 1, 'last' => 1]);

        if (!empty($units)) {
            $list = [];
            foreach ($units as $item)
            {
                $list[] = unserialize($item->data);
            }
            return $list;
        }

        return [];
    }

    /**
     * Получение списка целей
     * @return \common\models\vetis\VetisUnit[]|null
     */
    public function getPurposeList()
    {
        VetisPurpose::getUpdateData($this->org_id);

        $purposes = VetisPurpose::findAll(['active' => 1, 'last' => 1]);

        if (!empty($purposes)) {
            $list = [];
            foreach ($purposes as $item)
            {
               $list[] = unserialize($item->data);
            }
            return $list;
        }

        return [];
    }

    /**
     * Составление запроса на загрузку справочника едениц изхмерения
     * @param $options
     * @return getUnitChangesListRequest
     * @throws \Exception
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
     * @throws \Exception
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