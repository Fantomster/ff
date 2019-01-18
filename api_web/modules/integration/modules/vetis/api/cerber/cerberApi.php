<?php

namespace api_web\modules\integration\modules\vetis\api\cerber;

use common\models\vetis\VetisBusinessEntity;
use common\models\vetis\VetisForeignEnterprise;
use common\models\vetis\VetisRussianEnterprise;
use api_web\modules\integration\modules\vetis\api\baseApi;

/**
 * Class cerberApi
 *
 * @package api_web\modules\integration\modules\vetis\api\cerber
 */
class cerberApi extends baseApi
{

    /**
     *
     */
    public function init()
    {
        $this->system        = 'cerber';
        $this->wsdlClassName = Cerber::class;
        parent::init();
    }

    /**
     * Получение списка предприятий ХС
     * @return mixed
     */
    public function getActivityLocationList()
    {
        return VetisRussianEnterprise::find()->where(['owner_guid' => $this->issuerID])->all();
    }

    /**
     * Получение записи предприятия по UUID
     * @param $UUID
     * @return mixed|null
     */
    public function getEnterpriseByUuid($UUID)
    {
        VetisForeignEnterprise::getUpdateData($this->org_id);
        VetisRussianEnterprise::getUpdateData($this->org_id);

        if ($UUID == null) {
            return null;
        }

        $enterprise = VetisRussianEnterprise::findOne(['uuid' => $UUID, 'active' => true, 'last' => true]);

        if (empty($enterprise)) {
            $enterprise = VetisForeignEnterprise::findOne(['uuid' => $UUID, 'active' => true, 'last' => true]);
        }

        if (!empty($enterprise)) {
            return $enterprise->enterprise;
        }

        return null;
    }

    /**
     * Получение записи ХС по UUID
     * @param $UUID
     * @return mixed|null
     */
    public function getBusinessEntityByUuid($UUID)
    {
        VetisBusinessEntity::getUpdateData($this->org_id);

        $business = VetisBusinessEntity::findOne(['uuid' => $UUID, 'active' => true, 'last' => true]);

        if (!empty($business)) {
            return $business->businessEntity;
        }

        return null;
    }

    /**
     * Получение записи предприятия по GUID
     * @param string $GUID
     * @return mixed|null
     */
    public function getEnterpriseByGuid($GUID)
    {
        VetisForeignEnterprise::getUpdateData($this->org_id);
        VetisRussianEnterprise::getUpdateData($this->org_id);

        if ($GUID == null) {
            return null;
        }

        $enterprise = VetisRussianEnterprise::findOne(['guid' => $GUID, 'active' => true, 'last' => true]);

        if (empty($enterprise)) {
            $enterprise = VetisForeignEnterprise::findOne(['guid' => $GUID, 'active' => true, 'last' => true]);
        }

        if (!empty($enterprise)) {
            return $enterprise->enterprise;
        }
        return null;
    }

    /**
     * Получение записи ХС по GUID
     * @param string $GUID
     * @return mixed|null
     */
    public function getBusinessEntityByGuid($GUID)
    {
        VetisBusinessEntity::getUpdateData($this->org_id);

        $business = VetisBusinessEntity::findOne(['guid' => $GUID, 'active' => true, 'last' => true]);

        if (!empty($business)) {
            return $business->businessEntity;
        }

        return null;
    }

    /**
     * Поиск предприятия по названию и стране
     * @param $name
     * @param $country_guid
     * @return array
     */
    public function getForeignEnterpriseList($name, $country_guid)
    {
        VetisForeignEnterprise::getUpdateData($this->org_id);

        $result = VetisForeignEnterprise::find()->where(['active' => true, 'last' => true, 'country_guid' => $country_guid])->andWhere("MATCH (name) AGAINST ('$name*' IN BOOLEAN MODE)")->all();

        if (!empty($result)) {
            $list = [];
            foreach ($result as $item) {
                $list[] = $item->enterprise;
            }
            return $list;
        }

        return [];
    }

    /**
     * Поиск предприятия по названию в России
     * @param $name
     * @return array
     */
    public function getRussianEnterpriseList($name)
    {
        VetisRussianEnterprise::getUpdateData($this->org_id);

        $result = VetisRussianEnterprise::find()->where(['active' => true, 'last' => true])->andWhere("MATCH (name) AGAINST ('$name*' IN BOOLEAN MODE)")->all();

        if (!empty($result)) {
            $list = [];
            foreach ($result as $item) {
                $list[] = $item->enterprise;
            }
            return $list;
        }

        return [];
    }

    /**
     * Составление запроса на списка предприятий мира
     * @param $options
     * @return getForeignEnterpriseChangesListRequest
     * @throws \Exception
     */
    public function getForeignEnterpriseChangesList($options)
    {
        require_once (__DIR__ . "/Cerber.php");
        $request = new getForeignEnterpriseChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception(\Yii::t('api_web', 'startDate field is not specified', ['ru'=>'Начальная дата неуказана']));
        }

        $request->updateDateInterval            = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate   = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

    /**
     * Составление запроса на списка предприятий России
     * @param $options
     * @return getRussianEnterpriseChangesListRequest
     * @throws \Exception
     */
    public function getRussianEnterpriseChangesList($options)
    {
        require_once (__DIR__ . "/Cerber.php");
        $request = new getRussianEnterpriseChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception(\Yii::t('api_web', 'startDate field is not specified', ['ru'=>'Начальная дата неуказана']));
        }

        $request->updateDateInterval            = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate   = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

    /**
     * Составление запроса на списка ХС России
     * @param $options
     * @return getBusinessEntityChangesListRequest
     * @throws \Exception
     */
    public function getBusinessEntityChangesList($options)
    {
        require_once (__DIR__ . "/Cerber.php");
        $request = new getBusinessEntityChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception(\Yii::t('api_web', 'startDate field is not specified', ['ru'=>'Начальная дата неуказана']));
        }

        $request->updateDateInterval            = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate   = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

}
