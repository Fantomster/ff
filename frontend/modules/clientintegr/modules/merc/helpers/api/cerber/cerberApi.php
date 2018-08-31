<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\cerber;

use common\models\vetis\VetisBusinessEntity;
use common\models\vetis\VetisForeignEnterprise;
use common\models\vetis\VetisRussianEnterprise;
use frontend\modules\clientintegr\modules\merc\helpers\api\baseApi;
use Yii;

class cerberApi extends baseApi
{

    public function init()
    {
        $load = new Cerber();
        $this->system = 'cerber';
        $this->wsdlClassName = Cerber::class;
        parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * Получение списка предприятий ХС
     * @return mixed
     */
    public function getActivityLocationList()
    {
        $client = $this->getSoapClient('cerber');
        $request = new getActivityLocationListRequest();
        $request->businessEntity = new BusinessEntity();
        $request->businessEntity->guid = $this->issuerID;
        return $client->GetActivityLocationList($request);
    }

    /**
     * Получение записи предприятия по UUID
     * @param $UUID
     * @return mixed|null
     */
    public function getEnterpriseByUuid($UUID)
    {
        VetisForeignEnterprise::getUpdateData(Yii::$app->user->identity->organization_id);
        VetisRussianEnterprise::getUpdateData(Yii::$app->user->identity->organization_id);

        if ($UUID == null) {
            return null;
        }

        $enterprise = VetisRussianEnterprise::findOne(['uuid' => $UUID, 'active' => true, 'last' => 'true']);

        if (empty($enterprise)) {
            $enterprise = VetisForeignEnterprise::findOne(['uuid' => $UUID, 'active' => true, 'last' => 'true']);
        }

        if (!empty($enterprise)) {
            return unserialize($enterprise->data);
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
        VetisBusinessEntity::getUpdateData(Yii::$app->user->identity->organization_id);

        $business = VetisBusinessEntity::findOne(['uuid' => $UUID, 'active' => true, 'last' => 'true']);

        if (!empty($business)) {
            return unserialize($business->data);
        }

        return null;
    }

    /**
     * Получение записи предприятия по GUID
     * @param $UUID
     * @return mixed|null
     */
    public function getEnterpriseByGuid($GUID)
    {
        VetisForeignEnterprise::getUpdateData(Yii::$app->user->identity->organization_id);
        VetisRussianEnterprise::getUpdateData(Yii::$app->user->identity->organization_id);

        if ($GUID == null) {
            return null;
        }

        $enterprise = VetisRussianEnterprise::findOne(['guid' => $GUID, 'active' => true, 'last' => 'true']);

        if (empty($enterprise)) {
            $enterprise = VetisForeignEnterprise::findOne(['guid' => $GUID, 'active' => true, 'last' => 'true']);
        }

        if (!empty($enterprise)) {
            return unserialize($enterprise->data);
        }

        return null;
    }

    /**
     * Получение записи ХС по GUID
     * @param $UUID
     * @return mixed|null
     */
    public function getBusinessEntityByGuid($GUID)
    {
        VetisBusinessEntity::getUpdateData(Yii::$app->user->identity->organization_id);

        $business = VetisBusinessEntity::findOne(['guid' => $GUID, 'active' => true, 'last' => 'true']);

        if (!empty($business)) {
            return unserialize($business->data);
        }

        return null;
    }

    /**
     * Посик предприятия по названию и стране
     * @param $name
     * @param $country_guid
     * @return array
     */
    public function getForeignEnterpriseList($name, $country_guid)
    {
        VetisForeignEnterprise::getUpdateData(Yii::$app->user->identity->organization_id);

        $result = VetisForeignEnterprise::find()->where(['country_guid' => $country_guid])->andWhere(['like', 'name', $name])->one();

        if (!empty($result)) {
                $list = [];
                foreach ($result as $item)
                {
                    $list[] = unserialize($item->data);
                }
                return $list;
            }

        return [];
    }

    /**
     * Посик предприятия по названию в России
     * @param $name
     * @param $country_guid
     * @return array
     */
    public function getRussianEnterpriseList($name)
    {
        VetisRussianEnterprise::getUpdateData(Yii::$app->user->identity->organization_id);

        $result = VetisRussianEnterprise::find()->where(['like', 'name', $name])->one();

        if (!empty($result)) {
            $list = [];
            foreach ($result as $item)
            {
                $list[] = unserialize($item->data);
            }
            return $list;
        }

        return [];
    }

    /**
     * Составление запроса на списка предприятий мира
     * @param $options
     * @return getForeignEnterpriseChangesListRequest
     * @throws Exception
     */
    public function getForeignEnterpriseChangesList($options)
    {
        $request = new getForeignEnterpriseChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new Exception('startDate field is not specified');
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

    /**
     * Составление запроса на списка предприятий России
     * @param $options
     * @return getRussianEnterpriseChangesListRequest
     * @throws Exception
     */
    public function getRussianEnterpriseChangesList($options)
    {
        $request = new getRussianEnterpriseChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new Exception('startDate field is not specified');
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

    /**
     * Составление запроса на списка ХС России
     * @param $options
     * @return getBusinessEntityChangesListRequest
     * @throws Exception
     */
    public function getBusinessEntityChangesList($options)
    {
        $request = new getBusinessEntityChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new Exception('startDate field is not specified');
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }


}
